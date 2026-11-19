<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoImagen;
use App\Models\ProductoStock;
use App\Models\ProductoVariante;
use App\Models\Vendedor\Local;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    /* ---------------------------------------------------------------
     |  Helpers
     * --------------------------------------------------------------- */

    // Categorías globales (local_id NULL) + las propias del local
    private function categoriasDisponibles(Local $local)
    {
        return Categoria::where('activo', 1)
            ->where(function ($q) use ($local) {
                $q->whereNull('local_id')->orWhere('local_id', $local->id);
            })
            ->orderBy('orden')
            ->get();
    }

    private function categoriaValida($categoriaId, Local $local): bool
    {
        return Categoria::where('id', $categoriaId)
            ->where(function ($q) use ($local) {
                $q->whereNull('local_id')->orWhere('local_id', $local->id);
            })
            ->exists();
    }

    private function guardarStock(Local $local, $productoId, $varianteId, $stock, $stockMinimo, bool $manejaStock): void
    {
        ProductoStock::updateOrCreate(
            [
                'producto_id' => $productoId,
                'variante_id' => $varianteId,
                'local_id' => $local->id,
            ],
            [
                'stock' => (int) $stock,
                'stock_minimo' => (int) $stockMinimo,
                'maneja_stock' => $manejaStock,
            ]
        );
    }

    /**
     * Quita las variantes que ya no se usan.
     * Si una variante ya aparece en pedidos NO se borra (pedido_detalles.variante_id pasaría a NULL
     * y la cancelación de pedidos no podría devolver el stock): solo se desactiva.
     * Devuelve las rutas de imágenes que pueden borrarse del disco.
     */
    private function retirarVariantes(Producto $producto, array $conservar): array
    {
        $imagenes = [];

        ProductoVariante::where('producto_id', $producto->id)
            ->whereNotIn('id', $conservar)
            ->get()
            ->each(function ($variante) use (&$imagenes) {
                $enPedidos = DB::table('pedido_detalles')
                    ->where('variante_id', $variante->id)
                    ->exists();

                if ($enPedidos) {
                    $variante->update(['activo' => 0]);
                    return;
                }

                if ($variante->imagen) {
                    $imagenes[] = $variante->imagen;
                }

                $variante->delete(); // producto_stocks se borra en cascada
            });

        return $imagenes;
    }

    private function reglasProducto(bool $conId = false): array
    {
        $reglas = [
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'nombre' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],

            // 0 = sin variantes, 1 = con variantes
            'tiene_variantes' => ['required', 'in:0,1'],

            // Solo cuando NO tiene variantes (se guardan en la variante "Estándar")
            'precio' => ['required_if:tiene_variantes,0', 'nullable', 'numeric', 'min:0'],
            'precio_costo' => ['nullable', 'numeric', 'min:0'],
            'precio_oferta' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],

            'maneja_stock' => ['nullable'],
            'activo' => ['nullable'],
            'destacado' => ['nullable'],

            'imagen_principal' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagenes.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            // Solo cuando SÍ tiene variantes
            'variantes' => ['required_if:tiene_variantes,1', 'nullable', 'array', 'min:1'],
            'variantes.*.nombre' => ['required', 'string', 'max:150'],
            'variantes.*.valor' => ['nullable', 'string', 'max:150'],
            'variantes.*.precio_venta' => ['required', 'numeric', 'min:0'],
            'variantes.*.precio_costo' => ['nullable', 'numeric', 'min:0'],
            'variantes.*.precio_oferta' => ['nullable', 'numeric', 'min:0'],
            'variantes.*.sku' => ['nullable', 'string', 'max:100'],
            'variantes.*.imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'variantes.*.stock' => ['nullable', 'integer', 'min:0'],
            'variantes.*.stock_minimo' => ['nullable', 'integer', 'min:0'],
            'variantes.*.activo' => ['nullable'],
        ];

        if ($conId) {
            $reglas['variantes.*.id'] = ['nullable', 'integer'];
        }

        return $reglas;
    }

    private function mensajesProducto(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no debe pasar de 200 caracteres.',
            'precio.required_if' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número válido.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'variantes.required_if' => 'Agrega al menos una variante.',
            'variantes.*.nombre.required' => 'Cada variante necesita un nombre.',
            'variantes.*.precio_venta.required' => 'Cada variante necesita su precio de venta.',
            'imagen_principal.image' => 'La imagen principal debe ser una imagen válida.',
            'imagenes.*.image' => 'Las imágenes extras deben ser imágenes válidas.',
            'variantes.*.imagen.image' => 'La imagen de variante debe ser una imagen válida.',
        ];
    }

    /* ---------------------------------------------------------------
     |  Listado
     * --------------------------------------------------------------- */

    public function index($localId)
    {
        $local = Local::where('id', $localId)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        DB::statement('CALL actualizar_estado_local(?)', [$local->id]);
        $local->refresh();

        $productos = Producto::with([
                'categoria',
                'stock',
                'imagenPrincipal',
                'variantes.stock',
            ])
            ->where('local_id', $local->id)
            ->latest()
            ->get();

        return view('vendedor.productos.index', compact('local', 'productos'));
    }

    /* ---------------------------------------------------------------
     |  Crear
     * --------------------------------------------------------------- */

    public function create($localId)
    {
        $local = Local::where('id', $localId)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        DB::statement('CALL actualizar_estado_local(?)', [$local->id]);
        $local->refresh();

        $categorias = $this->categoriasDisponibles($local);

        return view('vendedor.productos.create', compact('local', 'categorias'));
    }

    public function store(Request $request, $localId)
    {
        // dd($request->all()); // DEBUG: descomenta para ver lo que llega del formulario

        $local = Local::where('id', $localId)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        $request->validate($this->reglasProducto(), $this->mensajesProducto());

        if ($request->categoria_id && !$this->categoriaValida($request->categoria_id, $local)) {
            return back()
                ->withInput()
                ->with('error', 'La categoría seleccionada no pertenece a este local.');
        }

        $tieneVariantes = (int) $request->tiene_variantes === 1;
        $manejaStock = $request->boolean('maneja_stock');
        $archivosNuevos = []; // se borran si la transacción falla

        try {
            DB::transaction(function () use ($request, $local, $tieneVariantes, $manejaStock, &$archivosNuevos) {

                $producto = Producto::create([
                    'local_id' => $local->id,
                    'categoria_id' => $request->categoria_id,
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'activo' => $request->boolean('activo'),
                    'destacado' => $request->boolean('destacado'),
                ]);

                // Sin variantes => una sola variante "Estándar"
                if ($tieneVariantes) {
                    $variantes = $request->variantes;
                } else {
                    $variantes = [[
                        'nombre' => 'Estándar',
                        'valor' => null,
                        'precio_venta' => $request->precio,
                        'precio_costo' => $request->precio_costo,
                        'precio_oferta' => $request->precio_oferta,
                        'sku' => $request->sku,
                        'stock' => $request->stock,
                        'stock_minimo' => $request->stock_minimo,
                        'activo' => 1,
                    ]];
                }

                foreach ($variantes as $index => $data) {
                    $imagenVariante = null;

                    if ($tieneVariantes && $request->hasFile("variantes.$index.imagen")) {
                        $imagenVariante = $request->file("variantes.$index.imagen")
                            ->store('productos/variantes', 'public');
                        $archivosNuevos[] = $imagenVariante;
                    }

                    $stock = (int) ($data['stock'] ?? 0);

                    $variante = ProductoVariante::create([
                        'producto_id' => $producto->id,
                        'nombre' => $data['nombre'],
                        'valor' => $data['valor'] ?? null,
                        'precio_venta' => $data['precio_venta'],
                        'precio_costo' => $data['precio_costo'] ?? 0,
                        'precio_oferta' => $data['precio_oferta'] ?? null,
                        'sku' => $data['sku'] ?? null,
                        'stock' => $stock,
                        'imagen' => $imagenVariante,
                        'activo' => $tieneVariantes ? (isset($data['activo']) ? 1 : 0) : 1,
                    ]);

                    $this->guardarStock($local, $producto->id, $variante->id, $stock, $data['stock_minimo'] ?? 0, $manejaStock);
                }

                if ($request->hasFile('imagen_principal')) {
                    $path = $request->file('imagen_principal')->store('productos', 'public');
                    $archivosNuevos[] = $path;

                    ProductoImagen::create([
                        'producto_id' => $producto->id,
                        'imagen' => $path,
                        'principal' => 1,
                        'orden' => 0,
                    ]);
                }

                if ($request->hasFile('imagenes')) {
                    foreach ($request->file('imagenes') as $index => $imagen) {
                        $path = $imagen->store('productos', 'public');
                        $archivosNuevos[] = $path;

                        ProductoImagen::create([
                            'producto_id' => $producto->id,
                            'imagen' => $path,
                            'principal' => 0,
                            'orden' => $index + 1,
                        ]);
                    }
                }
            });

            return redirect()
                ->route('vendedor.locales.productos.index', $local->id)
                ->with('success', 'Producto creado correctamente.');

        } catch (\Throwable $e) {
            foreach ($archivosNuevos as $archivo) {
                Storage::disk('public')->delete($archivo);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /* ---------------------------------------------------------------
     |  Editar
     * --------------------------------------------------------------- */

    public function edit($localId, $productoId)
    {
        $local = Local::where('id', $localId)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        $producto = Producto::with([
                'categoria',
                'imagenes',
                'imagenPrincipal',
                'variantes.stock',
            ])
            ->where('id', $productoId)
            ->where('local_id', $local->id)
            ->firstOrFail();

        // Un producto "sin variantes" es el que solo tiene activa la variante "Estándar"
        $activas = $producto->variantes->where('activo', 1)->values();

        $esSimple = $producto->variantes->isEmpty()
            || ($activas->count() === 1
                && $activas->first()->nombre === 'Estándar'
                && empty($activas->first()->valor));

        $tieneVariantes = !$esSimple;
        $varianteSimple = $esSimple ? $activas->first() : null;

        $categorias = $this->categoriasDisponibles($local);

        return view('vendedor.productos.edit', compact(
            'local',
            'producto',
            'categorias',
            'tieneVariantes',
            'varianteSimple'
        ));
    }

    public function update(Request $request, $localId, $productoId)
    {
        $local = Local::where('id', $localId)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        $producto = Producto::where('id', $productoId)
            ->where('local_id', $local->id)
            ->firstOrFail();

        $request->validate($this->reglasProducto(true), $this->mensajesProducto());

        if ($request->categoria_id && !$this->categoriaValida($request->categoria_id, $local)) {
            return back()
                ->withInput()
                ->with('error', 'La categoría seleccionada no pertenece a este local.');
        }

        $tieneVariantes = (int) $request->tiene_variantes === 1;
        $manejaStock = $request->boolean('maneja_stock');
        $archivosNuevos = [];   // se borran si falla
        $archivosViejos = [];   // se borran solo si todo sale bien

        try {
            DB::transaction(function () use ($request, $local, $producto, $tieneVariantes, $manejaStock, &$archivosNuevos, &$archivosViejos) {

                $producto->update([
                    'categoria_id' => $request->categoria_id,
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'activo' => $request->boolean('activo'),
                    'destacado' => $request->boolean('destacado'),
                ]);

                // Stock heredado sin variante (esquema anterior): ya no se usa
                ProductoStock::where('producto_id', $producto->id)
                    ->whereNull('variante_id')
                    ->delete();

                if ($tieneVariantes) {
                    $conservar = [];

                    foreach ($request->variantes as $index => $data) {
                        $variante = null;

                        if (!empty($data['id'])) {
                            $variante = ProductoVariante::where('id', $data['id'])
                                ->where('producto_id', $producto->id)
                                ->first();
                        }

                        $imagen = $variante ? $variante->imagen : null;

                        if ($request->hasFile("variantes.$index.imagen")) {
                            if ($imagen) {
                                $archivosViejos[] = $imagen;
                            }

                            $imagen = $request->file("variantes.$index.imagen")
                                ->store('productos/variantes', 'public');
                            $archivosNuevos[] = $imagen;
                        }

                        $stock = (int) ($data['stock'] ?? 0);

                        $datos = [
                            'nombre' => $data['nombre'],
                            'valor' => $data['valor'] ?? null,
                            'precio_venta' => $data['precio_venta'],
                            'precio_costo' => $data['precio_costo'] ?? 0,
                            'precio_oferta' => $data['precio_oferta'] ?? null,
                            'sku' => $data['sku'] ?? null,
                            'stock' => $stock,
                            'imagen' => $imagen,
                            'activo' => isset($data['activo']) ? 1 : 0,
                        ];

                        if ($variante) {
                            $variante->update($datos);
                        } else {
                            $variante = ProductoVariante::create($datos + ['producto_id' => $producto->id]);
                        }

                        $conservar[] = $variante->id;

                        $this->guardarStock($local, $producto->id, $variante->id, $stock, $data['stock_minimo'] ?? 0, $manejaStock);
                    }

                    $archivosViejos = array_merge($archivosViejos, $this->retirarVariantes($producto, $conservar));
                } else {
                    // Sin variantes: una sola variante "Estándar"
                    $stock = (int) ($request->stock ?? 0);

                    $datos = [
                        'nombre' => 'Estándar',
                        'valor' => null,
                        'precio_venta' => $request->precio,
                        'precio_costo' => $request->precio_costo ?? 0,
                        'precio_oferta' => $request->precio_oferta,
                        'sku' => $request->sku,
                        'stock' => $stock,
                        'activo' => 1,
                    ];

                    $variante = ProductoVariante::where('producto_id', $producto->id)
                        ->where('nombre', 'Estándar')
                        ->first();

                    if ($variante) {
                        $variante->update($datos);
                    } else {
                        $variante = ProductoVariante::create($datos + ['producto_id' => $producto->id]);
                    }

                    $this->guardarStock($local, $producto->id, $variante->id, $stock, $request->stock_minimo ?? 0, $manejaStock);

                    $archivosViejos = array_merge($archivosViejos, $this->retirarVariantes($producto, [$variante->id]));
                }

                // Imágenes del producto
                if ($request->hasFile('imagen_principal')) {
                    $actual = ProductoImagen::where('producto_id', $producto->id)
                        ->where('principal', 1)
                        ->first();

                    if ($actual) {
                        $archivosViejos[] = $actual->imagen;
                        $actual->delete();
                    }

                    $path = $request->file('imagen_principal')->store('productos', 'public');
                    $archivosNuevos[] = $path;

                    ProductoImagen::create([
                        'producto_id' => $producto->id,
                        'imagen' => $path,
                        'principal' => 1,
                        'orden' => 0,
                    ]);
                }

                if ($request->hasFile('imagenes')) {
                    $orden = (int) ProductoImagen::where('producto_id', $producto->id)->max('orden');

                    foreach ($request->file('imagenes') as $imagen) {
                        $path = $imagen->store('productos', 'public');
                        $archivosNuevos[] = $path;

                        ProductoImagen::create([
                            'producto_id' => $producto->id,
                            'imagen' => $path,
                            'principal' => 0,
                            'orden' => ++$orden,
                        ]);
                    }
                }
            });

            // Todo salió bien: ahora sí se borran los archivos reemplazados
            foreach ($archivosViejos as $archivo) {
                Storage::disk('public')->delete($archivo);
            }

            return redirect()
                ->route('vendedor.locales.productos.index', $local->id)
                ->with('success', 'Producto actualizado correctamente.');

        } catch (\Throwable $e) {
            foreach ($archivosNuevos as $archivo) {
                Storage::disk('public')->delete($archivo);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /* ---------------------------------------------------------------
     |  Estado / eliminar / listado general
     * --------------------------------------------------------------- */

    public function cambiarEstado($localId, $productoId)
    {
        $local = Local::where('id', $localId)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        DB::statement('CALL actualizar_estado_local(?)', [$local->id]);
        $local->refresh();

        $producto = Producto::where('id', $productoId)
            ->where('local_id', $local->id)
            ->firstOrFail();

        if (!$producto->activo) {
            if (!$local->activo) {
                return redirect()
                    ->route('vendedor.locales.productos.index', $local->id)
                    ->with('error', 'No puedes publicar productos porque el local está inactivo.');
            }

            if ($local->estado_local === 'pausado') {
                return redirect()
                    ->route('vendedor.locales.productos.index', $local->id)
                    ->with('error', 'No puedes publicar este producto porque el local está pausado.');
            }

            if ($local->estado_local === 'cerrado') {
                return redirect()
                    ->route('vendedor.locales.productos.index', $local->id)
                    ->with('error', 'No puedes publicar este producto porque el local está cerrado.');
            }
        }

        try {
            $producto->update([
                'activo' => !$producto->activo,
            ]);
        } catch (\Throwable $e) {
            return redirect()
                ->route('vendedor.locales.productos.index', $local->id)
                ->with('error', $e->getMessage());
        }

        $mensaje = $producto->activo
            ? 'Producto publicado correctamente.'
            : 'Producto pausado correctamente. Ya no será público.';

        return redirect()
            ->route('vendedor.locales.productos.index', $local->id)
            ->with('success', $mensaje);
    }

    public function destroy($localId, $productoId)
    {
        $local = Local::where('id', $localId)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        $producto = Producto::where('id', $productoId)
            ->where('local_id', $local->id)
            ->firstOrFail();

        $producto->update([
            'activo' => 0,
        ]);

        return redirect()
            ->route('vendedor.locales.productos.index', $local->id)
            ->with('success', 'Producto eliminado correctamente.');
    }

    public function todos(Request $request)
    {
        $buscar = $request->get('buscar');
        $estado = $request->get('estado');
        $localId = $request->get('local_id');

        $locales = Local::where('vendedor_id', Auth::id())
            ->orderBy('nombre')
            ->get();

        $productos = Producto::with(['stock', 'categoria', 'imagenPrincipal', 'local', 'variantes'])
            ->whereHas('local', function ($query) {
                $query->where('vendedor_id', Auth::id());
            })
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'LIKE', "%{$buscar}%")
                        ->orWhere('descripcion', 'LIKE', "%{$buscar}%")
                        // `sku` ya no está en productos, está en producto_variantes
                        ->orWhereHas('variantes', function ($v) use ($buscar) {
                            $v->where('sku', 'LIKE', "%{$buscar}%");
                        });
                });
            })
            ->when($estado !== null && $estado !== '', function ($query) use ($estado) {
                $query->where('activo', $estado);
            })
            ->when($localId, function ($query) use ($localId) {
                $query->where('local_id', $localId);
            })
            ->latest()
            ->get();

        return view('vendedor.productos.prodectosforlocales', compact(
            'productos',
            'locales',
            'buscar',
            'estado',
            'localId'
        ));
    }
}