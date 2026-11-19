<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Direccion;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use App\Models\ProductoStock;
use App\Models\ProductoVariante;
use App\Models\Vendedor\Local;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CarritoController extends Controller
{
    /* ---------------------------------------------------------------
     |  Helpers
     |  El precio SIEMPRE sale de producto_variantes (también en los
     |  productos sin variantes, que tienen una variante "Estándar").
     * --------------------------------------------------------------- */

    // Precio que paga el cliente: la oferta solo cuenta si es válida y menor al precio de venta
    private function precioFinal($variante): float
    {
        $venta = (float) $variante->precio_venta;
        $oferta = (float) $variante->precio_oferta;

        return ($oferta > 0 && $oferta < $venta) ? $oferta : $venta;
    }

    // "Tamaño: Grande". Para la variante "Estándar" (producto sin variantes) devuelve null
    private function nombreVariante($variante): ?string
    {
        if (!$variante) {
            return null;
        }

        if ($variante->nombre === 'Estándar' && empty($variante->valor)) {
            return null;
        }

        return $variante->nombre . ($variante->valor ? ': ' . $variante->valor : '');
    }

    /**
     * Costo de envío del local hacia una ciudad.
     *
     * 1) Zona de entrega ACTIVA del local para esa ciudad (local_zonas_entrega) -> costo de la zona.
     * 2) Sin zona pero misma ciudad que el local -> locales.costo_envio.
     * 3) Si no -> null (el local no entrega ahí).
     *
     * Requiere que $local tenga cargada la relación zonasEntrega.
     * Es la misma regla que usa el catálogo, así lo que ve el cliente es lo que se cobra.
     */
    private function costoEnvioA($local, ?string $ciudad): ?float
    {
        $ciudad = mb_strtolower(trim((string) $ciudad));

        if ($ciudad === '') {
            return null;
        }

        $zona = $local->zonasEntrega
            ->where('activo', 1)
            ->first(fn ($z) => mb_strtolower(trim($z->ciudad)) === $ciudad);

        if ($zona) {
            return (float) $zona->costo_envio;
        }

        if (mb_strtolower(trim((string) $local->ciudad)) === $ciudad) {
            return (float) ($local->costo_envio ?? 0);
        }

        return null;
    }

    private function stockDeVariante($productoId, $varianteId, $localId, bool $bloquear = false)
    {
        $consulta = ProductoStock::where('producto_id', $productoId)
            ->where('variante_id', $varianteId)
            ->where('local_id', $localId);

        if ($bloquear) {
            $consulta->lockForUpdate();
        }

        return $consulta->first();
    }

    private function recalcularTotal(Carrito $carrito): void
    {
        $carrito->total = $carrito->items()->sum('subtotal');
        $carrito->save();
    }

    /* ---------------------------------------------------------------
     |  Ver carrito
     * --------------------------------------------------------------- */

    public function index()
    {
        $clienteId = Auth::id();

        $carritosBd = Carrito::with([
                'local.zonasEntrega',
                'items.producto.imagenPrincipal',
                'items.variante',
            ])
            ->where('cliente_id', $clienteId)
            ->where('estado', 'activo')
            ->get();

        $direcciones = Direccion::where('user_id', $clienteId)
            ->latest()
            ->get();

        $direccionPrincipal = $direcciones->firstWhere('principal', 1);

        $carrito = [];

        foreach ($carritosBd as $cBd) {
            DB::statement('CALL actualizar_estado_local(?)', [$cBd->local_id]);

            $local = $cBd->local;
            if (!$local) {
                continue;
            }

            // Stock de todas las variantes del carrito en una sola consulta
            $stocks = ProductoStock::where('local_id', $cBd->local_id)
                ->whereIn('variante_id', $cBd->items->pluck('variante_id')->filter())
                ->get()
                ->keyBy('variante_id');

            $items = [];
            $totalCarritoActualizado = 0;

            foreach ($cBd->items as $item) {
                $producto = $item->producto;
                $variante = $item->variante;

                // Producto o variante eliminados/desactivados: se quitan del carrito
                if (!$producto || !$producto->activo || !$variante || !$variante->activo) {
                    $item->delete();
                    continue;
                }

                // 1. Precio actual desde la variante (oferta válida o precio de venta)
                $precioActual = $this->precioFinal($variante);
                $subtotalActual = $precioActual * $item->cantidad;

                // 2. Sincronizar si cambió el precio o el subtotal
                if (round((float) $item->precio_unitario, 2) !== round($precioActual, 2)
                    || round((float) $item->subtotal, 2) !== round($subtotalActual, 2)) {
                    $item->precio_unitario = $precioActual;
                    $item->subtotal = $subtotalActual;
                    $item->save();
                }

                $totalCarritoActualizado += $subtotalActual;

                $stock = $stocks->get($variante->id);
                $stockDisponible = !$stock ? 0 : ($stock->maneja_stock ? (int) $stock->stock : null);

                $nombreVariante = $this->nombreVariante($variante);

                $items[$item->id] = [
                    'item_id' => $item->id,
                    'producto_id' => $item->producto_id,
                    'variante_id' => $item->variante_id,
                    'local_id' => $cBd->local_id,
                    'nombre' => $producto->nombre ?? '',
                    'variante_nombre' => $nombreVariante,
                    'tipo_producto' => $nombreVariante === null ? 'simple' : 'variable',
                    'precio' => $precioActual,
                    'precio_normal' => (float) $variante->precio_venta,
                    'tiene_oferta' => $precioActual < (float) $variante->precio_venta,
                    'cantidad' => $item->cantidad,
                    'subtotal' => $subtotalActual,
                    'stock_disponible' => $stockDisponible, // null = sin límite
                    'imagen' => $variante->imagen ?? $producto->imagenPrincipal->imagen ?? null,
                ];
            }

            // Si el carrito se quedó sin ítems válidos, se borra el registro
            if (empty($items)) {
                $cBd->delete();
                continue;
            }

            if (round((float) $cBd->total, 2) !== round($totalCarritoActualizado, 2)) {
                $cBd->total = $totalCarritoActualizado;
                $cBd->save();
            }

            // Costo de envío de ESTE local hacia cada dirección del cliente (null = no entrega ahí)
            $enviosPorDireccion = [];

            foreach ($direcciones as $direccion) {
                $enviosPorDireccion[$direccion->id] = $this->costoEnvioA($local, $direccion->ciudad);
            }

            $carrito[$cBd->local_id] = [
                'carrito_id' => $cBd->id,
                'local_id' => $cBd->local_id,
                'local_nombre' => $local->nombre,
                // Costo hacia la dirección principal (referencia para el encabezado)
                'costo_envio' => $direccionPrincipal ? ($enviosPorDireccion[$direccionPrincipal->id] ?? null) : null,
                'envios' => $enviosPorDireccion,
                'permite_delivery' => $local->permite_delivery ?? 0,
                'permite_recoger' => $local->permite_recoger ?? 1,
                'productos' => $items,
            ];
        }

        return view('cliente.carrito.index', compact('direcciones', 'carrito'));
    }

    /* ---------------------------------------------------------------
     |  Agregar al carrito
     * --------------------------------------------------------------- */

    public function agregar(Request $request, $productoId)
    {
        $request->merge(['producto_id' => $productoId]);

        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'variante_id' => 'required|exists:producto_variantes,id',
            'cantidad' => 'required|integer|min:1|max:999',
        ], [
            'variante_id.required' => 'Selecciona una variante.',
        ]);

        $clienteId = Auth::id();
        $cantidad = (int) $request->cantidad;

        try {
            DB::transaction(function () use ($request, $productoId, $clienteId, $cantidad) {

                // Producto público y local activo
                $producto = Producto::where('id', $productoId)
                    ->where('activo', 1)
                    ->firstOrFail();

                $local = Local::where('id', $producto->local_id)
                    ->where('activo', 1)
                    ->firstOrFail();

                // La variante debe ser de este producto y estar activa
                $variante = ProductoVariante::where('id', $request->variante_id)
                    ->where('producto_id', $producto->id)
                    ->where('activo', 1)
                    ->first();

                if (!$variante) {
                    throw new \Exception('La variante seleccionada no está disponible.');
                }

                $precioUnitario = $this->precioFinal($variante);

                // Carrito ACTIVO del cliente en este local (uno comprado no se reutiliza)
                $carrito = Carrito::firstOrCreate(
                    [
                        'cliente_id' => $clienteId,
                        'local_id' => $local->id,
                        'estado' => 'activo',
                    ],
                    ['total' => 0]
                );

                $item = CarritoItem::where('carrito_id', $carrito->id)
                    ->where('producto_id', $producto->id)
                    ->where('variante_id', $variante->id)
                    ->first();

                $nuevaCantidad = $cantidad + ($item ? (int) $item->cantidad : 0);

                // Stock de la variante en este local
                $stock = $this->stockDeVariante($producto->id, $variante->id, $local->id);

                if (!$stock) {
                    throw new \Exception('Este producto no tiene stock configurado.');
                }

                if ($stock->maneja_stock && $nuevaCantidad > $stock->stock) {
                    throw new \Exception(
                        $stock->stock > 0
                            ? 'Solo hay ' . $stock->stock . ' disponibles (ya tienes ' . ($nuevaCantidad - $cantidad) . ' en tu carrito).'
                            : 'Este producto no tiene stock.'
                    );
                }

                if ($item) {
                    $item->update([
                        'cantidad' => $nuevaCantidad,
                        'precio_unitario' => $precioUnitario,
                        'subtotal' => $precioUnitario * $nuevaCantidad,
                    ]);
                } else {
                    CarritoItem::create([
                        'carrito_id' => $carrito->id,
                        'producto_id' => $producto->id,
                        'variante_id' => $variante->id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $precioUnitario,
                        'subtotal' => $precioUnitario * $cantidad,
                    ]);
                }

                $this->recalcularTotal($carrito);
            });

            return back()->with('success', 'Producto agregado al carrito correctamente.');

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /* ---------------------------------------------------------------
     |  Cantidad / eliminar
     * --------------------------------------------------------------- */

    public function actualizarCantidad(Request $request, $localId, $itemId)
    {
        $request->validate([
            'cantidad' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $item = CarritoItem::where('id', $itemId)
            ->whereHas('carrito', function ($q) use ($localId) {
                $q->where('cliente_id', Auth::id())
                  ->where('local_id', $localId)
                  ->where('estado', 'activo');
            })->first();

        if (!$item) {
            return back()->with('error', 'El producto no se encuentra en el carrito.');
        }

        $cantidad = (int) $request->cantidad;

        // No permitir más que el stock disponible de esa variante
        $stock = $this->stockDeVariante($item->producto_id, $item->variante_id, $localId);

        if ($stock && $stock->maneja_stock && $cantidad > $stock->stock) {
            return back()->with('error', 'Solo hay ' . $stock->stock . ' disponibles de este producto.');
        }

        $item->cantidad = $cantidad;
        $item->subtotal = $cantidad * $item->precio_unitario;
        $item->save();

        $this->recalcularTotal($item->carrito);

        return back()->with('success', 'Cantidad actualizada correctamente.');
    }

    /* ---------------------------------------------------------------
     |  Eliminar ítem del carrito
     * --------------------------------------------------------------- */

    public function eliminarItem($localId, $itemId = null)
    {
        // La ruta puede traer {localId}/{itemId} (como usa la vista) o solo {itemId}
        $itemId = $itemId ?? $localId;

        $item = CarritoItem::where('id', $itemId)
            ->whereHas('carrito', function ($q) {
                $q->where('cliente_id', Auth::id())
                  ->where('estado', 'activo');
            })->first();

        if ($item) {
            $carrito = $item->carrito;
            $item->delete();

            if ($carrito->items()->count() === 0) {
                $carrito->delete();
            } else {
                $this->recalcularTotal($carrito);
            }

            return back()->with('success', 'Producto eliminado del carrito.');
        }

        return back()->with('error', 'El producto no fue encontrado.');
    }

    /* ---------------------------------------------------------------
     |  Confirmar pedido(s)
     * --------------------------------------------------------------- */

    public function confirmarLocal(Request $request, $localId)
    {
        $carrito = Carrito::with('items')
            ->where('cliente_id', Auth::id())
            ->where('local_id', $localId)
            ->where('estado', 'activo')
            ->first();

        if (!$carrito || $carrito->items->isEmpty()) {
            return redirect()
                ->route('cliente.carrito.index')
                ->with('error', 'No hay productos de este local en el carrito.');
        }

        try {
            DB::transaction(function () use ($carrito, $request) {
                $this->crearPedidoDesdeCarrito($carrito, $request);

                // Marcar el carrito como comprado
                $carrito->estado = 'comprado';
                $carrito->save();
            });

            return redirect()
                ->route('cliente.pedidos.ver')
                ->with('success', 'Pedido confirmado correctamente.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('cliente.carrito.index')
                ->with('error', $e->getMessage());
        }
    }

    public function confirmarTodos(Request $request)
    {
        $carritos = Carrito::with('items')
            ->where('cliente_id', Auth::id())
            ->where('estado', 'activo')
            ->get();

        if ($carritos->isEmpty()) {
            return redirect()
                ->route('cliente.carrito.index')
                ->with('error', 'Tu carrito está vacío.');
        }

        try {
            DB::transaction(function () use ($carritos, $request) {
                foreach ($carritos as $carrito) {
                    if ($carrito->items->isNotEmpty()) {
                        $this->crearPedidoDesdeCarrito($carrito, $request);
                        $carrito->estado = 'comprado';
                        $carrito->save();
                    }
                }
            });

            return redirect()
                ->route('cliente.pedidos.ver')
                ->with('success', 'Todos los pedidos fueron confirmados correctamente.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('cliente.carrito.index')
                ->with('error', $e->getMessage());
        }
    }

    private function crearPedidoDesdeCarrito(Carrito $carrito, Request $request)
    {
        $localId = $carrito->local_id;
        DB::statement('CALL actualizar_estado_local(?)', [$localId]);

        $local = Local::with('zonasEntrega')
            ->where('id', $localId)
            ->where('activo', 1)
            ->firstOrFail();

        if ($local->estado_local === 'pausado') {
            throw new \Exception('No puedes pedir en este local porque está pausado.');
        }

        if ($local->estado_local === 'cerrado') {
            throw new \Exception('No puedes pedir en este local porque está cerrado.');
        }

        $tipoEntrega = $request->tipo_entrega ?? 'recoger';
        $metodoPago = $request->metodo_pago ?? 'efectivo';
        $direccionId = null;
        $costoEnvio = 0;

        if ($tipoEntrega === 'delivery') {
            if (!$local->permite_delivery) {
                throw new \Exception('Este local no permite entrega a domicilio.');
            }

            if (!$request->direccion_id) {
                throw new \Exception('Selecciona una dirección de entrega.');
            }

            $direccion = Direccion::where('id', $request->direccion_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$direccion) {
                throw new \Exception('La dirección seleccionada no es válida.');
            }

            // Mismo cálculo que se muestra en el catálogo y en el carrito
            $costoEnvio = $this->costoEnvioA($local, $direccion->ciudad);

            if ($costoEnvio === null) {
                throw new \Exception('Este local no entrega en la ciudad de tu dirección.');
            }

            $direccionId = $direccion->id;
        }

        if ($tipoEntrega === 'recoger' && !$local->permite_recoger) {
            throw new \Exception('Este local no permite recoger en local.');
        }

        $itemsPreparados = [];
        $subtotal = 0;

        foreach ($carrito->items as $item) {
            $producto = Producto::where('id', $item->producto_id)
                ->where('local_id', $local->id)
                ->where('activo', 1)
                ->firstOrFail();

            $cantidad = (int) $item->cantidad;

            if ($cantidad <= 0) {
                throw new \Exception('Cantidad inválida para: ' . $producto->nombre);
            }

            // Todo producto (simple o con variantes) se compra por una variante
            if (!$item->variante_id) {
                throw new \Exception('Falta seleccionar variante para: ' . $producto->nombre);
            }

            $variante = ProductoVariante::where('id', $item->variante_id)
                ->where('producto_id', $producto->id)
                ->where('activo', 1)
                ->first();

            if (!$variante) {
                throw new \Exception('La variante seleccionada ya no está disponible para: ' . $producto->nombre);
            }

            // El precio se toma de la variante en este momento (no del carrito)
            $precioUnitario = $this->precioFinal($variante);
            $nombreVariante = $this->nombreVariante($variante);
            $etiqueta = $producto->nombre . ($nombreVariante ? ' - ' . $nombreVariante : '');

            $stock = $this->stockDeVariante($producto->id, $variante->id, $local->id, true);

            if (!$stock) {
                throw new \Exception('No hay stock configurado para: ' . $etiqueta);
            }

            if ($stock->maneja_stock && $stock->stock < $cantidad) {
                throw new \Exception('No hay suficiente stock para: ' . $etiqueta);
            }

            $subtotalItem = $precioUnitario * $cantidad;
            $subtotal += $subtotalItem;

            $itemsPreparados[] = [
                'producto' => $producto,
                'variante' => $variante,
                'stock' => $stock,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotalItem,
                'nombre_variante' => $nombreVariante,
            ];
        }

        if ($subtotal < ($local->minimo_pedido ?? 0)) {
            throw new \Exception('El pedido mínimo para ' . $local->nombre . ' es de $' . number_format($local->minimo_pedido, 2));
        }

        $total = $subtotal + $costoEnvio;

        $pedido = Pedido::create([
            'codigo' => 'PED-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
            'cliente_id' => Auth::id(),
            'local_id' => $local->id,
            'direccion_id' => $direccionId,
            'tipo_entrega' => $tipoEntrega,
            'metodo_pago' => $metodoPago,
            'estado_pago' => 'pendiente',
            'estado_pedido' => 'pendiente',
            'subtotal' => $subtotal,
            'costo_envio' => $costoEnvio,
            'descuento' => 0,
            'total' => $total,
            'notas_cliente' => $request->notas_cliente,
        ]);

        foreach ($itemsPreparados as $item) {
            PedidoDetalle::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $item['producto']->id,
                'variante_id' => $item['variante']->id,
                'nombre_producto' => $item['producto']->nombre,
                'nombre_variante' => $item['nombre_variante'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'subtotal' => $item['subtotal'],
            ]);

            // Solo se descuenta si controla stock (cancelar_pedido_cliente también
            // devuelve stock únicamente cuando maneja_stock = 1)
            if ($item['stock']->maneja_stock) {
                $item['stock']->decrement('stock', $item['cantidad']);
            }
        }

        Notificacion::create([
            'user_id' => $local->vendedor_id,
            'titulo' => 'Nuevo pedido recibido',
            'mensaje' => 'Tienes un nuevo pedido de $' . number_format($total, 2) . ' en ' . $local->nombre,
            'tipo' => 'pedido',
            'url' => route('vendedor.pedidos.show', $pedido->id),
            'leida' => 0,
        ]);

        return $pedido;
    }
}