<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\ProductoStock;
use App\Models\Vendedor\Local;
use Illuminate\Http\Request;
use App\Models\Favorito;
use App\Models\Direccion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CatalogoController extends Controller
{
    /* ---------------------------------------------------------------
     |  Helpers: precio y disponibilidad salen de las VARIANTES
     |  (productos ya no tiene precio ni stock)
     * --------------------------------------------------------------- */

    // Precio que paga el cliente: la oferta solo cuenta si es válida y menor al precio de venta
    private function precioFinal($variante): float
    {
        $venta = (float) $variante->precio_venta;
        $oferta = (float) $variante->precio_oferta;

        return ($oferta > 0 && $oferta < $venta) ? $oferta : $venta;
    }

    /**
     * Agrega datos calculados (solo para las vistas, no se guardan) a cada producto
     * y a sus variantes activas:
     *
     * Variante:  precio_final, tiene_oferta, stock_disponible (null = sin límite), disponible
     * Producto:  es_variable, precio_desde, precio_antes (precio sin oferta, si la hay), disponible
     */
    private function prepararProductos(Collection $productos, int $localId): void
    {
        if ($productos->isEmpty()) {
            return;
        }

        // Una sola consulta con el stock de todos los productos de este local
        $stocks = ProductoStock::where('local_id', $localId)
            ->whereIn('producto_id', $productos->pluck('id'))
            ->get()
            ->keyBy('variante_id');

        foreach ($productos as $producto) {
            foreach ($producto->variantes as $variante) {
                $fila = $stocks->get($variante->id);

                $variante->precio_final = $this->precioFinal($variante);
                $variante->tiene_oferta = $variante->precio_final < (float) $variante->precio_venta;

                if (!$fila) {
                    // Sin registro de stock: no se puede vender
                    $variante->stock_disponible = 0;
                    $variante->disponible = false;
                } elseif (!$fila->maneja_stock) {
                    // No controla stock: siempre disponible
                    $variante->stock_disponible = null;
                    $variante->disponible = true;
                } else {
                    $variante->stock_disponible = (int) $fila->stock;
                    $variante->disponible = $variante->stock_disponible > 0;
                }
            }

            $variantes = $producto->variantes;
            $masBarata = $variantes->sortBy('precio_final')->first();

            $unica = $variantes->count() === 1 ? $variantes->first() : null;

            // "Sin variantes" = una sola variante llamada "Estándar"
            $producto->es_variable = !($unica && $unica->nombre === 'Estándar' && empty($unica->valor));
            $producto->precio_desde = $masBarata ? $masBarata->precio_final : 0;
            $producto->precio_antes = ($masBarata && $masBarata->tiene_oferta) ? (float) $masBarata->precio_venta : null;
            $producto->disponible = $variantes->contains('disponible', true);
        }
    }

    // Solo variantes activas, de la más barata a la más cara
    private function soloVariantesActivas()
    {
        return function ($query) {
            $query->where('activo', 1)->orderBy('precio_venta');
        };
    }

    /**
     * Costo de envío del local hacia una ciudad.
     *
     * 0) Si el local no permite delivery, devuelve null (solo recoger en tienda).
     * 1) Si el local tiene una zona de entrega ACTIVA para esa ciudad (local_zonas_entrega), se usa su costo.
     * 2) Si no hay zona pero la ciudad es la del propio local, se usa locales.costo_envio.
     * 3) Si no, devuelve null: el local no entrega ahí.
     *
     * Requiere que $local tenga cargada la relación zonasEntrega.
     */
    private function costoEnvioA(Local $local, ?string $ciudad): ?float
    {
        // 0) El local no hace entregas a domicilio
        if (!$local->permite_delivery) {
            return null;
        }

        $ciudad = mb_strtolower(trim((string) $ciudad));

        if ($ciudad === '') {
            return null;
        }

        // 1) Zona de entrega activa para esa ciudad
        $zona = $local->zonasEntrega
            ->where('activo', 1)
            ->first(fn ($z) => mb_strtolower(trim($z->ciudad)) === $ciudad);

        if ($zona) {
            return (float) $zona->costo_envio;
        }

        // 2) Misma ciudad que el local
        if (mb_strtolower(trim((string) $local->ciudad)) === $ciudad) {
            return (float) ($local->costo_envio ?? 0);
        }

        // 3) No entrega ahí
        return null;
    }

    /* ---------------------------------------------------------------
     |  Catálogo
     * --------------------------------------------------------------- */

    public function index(Request $request)
{
    $buscar = $request->get('buscar');

    $direccionPrincipal = Direccion::where('user_id', Auth::id())
        ->where('principal', 1)
        ->first();

    if (!$direccionPrincipal) {
        $locales = collect();

        return view('cliente.catalogo.index', compact('locales', 'buscar', 'direccionPrincipal'))
            ->with('error', 'Agrega una dirección principal para ver locales disponibles en tu zona.');
    }

    $ciudadCliente = trim($direccionPrincipal->ciudad);

    $locales = Local::with(['zonasEntrega', 'horarios'])
        ->where('activo', 1)
        ->whereIn('estado_local', ['abierto', 'pausado', 'cerrado'])
        ->where(function ($query) use ($ciudadCliente) {
            // Coincide por la ciudad origen del local O por zona de entrega activa
            $query->whereRaw('LOWER(ciudad) = ?', [mb_strtolower($ciudadCliente)])
                ->orWhereHas('zonasEntrega', function ($q) use ($ciudadCliente) {
                    $q->where('activo', 1)
                      ->whereRaw('LOWER(ciudad) = ?', [mb_strtolower($ciudadCliente)]);
                });
        })
        ->when($buscar, function ($query) use ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                    ->orWhere('descripcion', 'LIKE', "%{$buscar}%")
                    ->orWhere('ciudad', 'LIKE', "%{$buscar}%")
                    ->orWhereHas('zonasEntrega', function ($zona) use ($buscar) {
                        $zona->where('ciudad', 'LIKE', "%{$buscar}%");
                    })
                    ->orWhereHas('productos', function ($producto) use ($buscar) {
                        $producto->where('activo', 1)
                            ->where(function ($p) use ($buscar) {
                                $p->where('nombre', 'LIKE', "%{$buscar}%")
                                  ->orWhere('descripcion', 'LIKE', "%{$buscar}%");
                            });
                    });
            });
        })
        ->latest()
        ->get();

    // Datos calculados solo para la vista (no se guardan)
    $locales->each(function ($local) use ($ciudadCliente) {
        $costo = $this->costoEnvioA($local, $ciudadCliente);

        $local->envio_disponible = $costo !== null;   // false = sin delivery o no cubre la ciudad
        $local->costo_envio_cliente = $costo ?? 0;
    });

    return view('cliente.catalogo.index', compact(
        'locales',
        'buscar',
        'direccionPrincipal'
    ));
}

    /* ---------------------------------------------------------------
     |  Productos de un local
     * --------------------------------------------------------------- */

    public function local($localId)
    {
        $direccionPrincipal = Direccion::where('user_id', Auth::id())
            ->where('principal', 1)
            ->first();

        if (!$direccionPrincipal) {
            return redirect()
                ->route('cliente.catalogo.index')
                ->with('error', 'Agrega una dirección principal para ver locales disponibles.');
        }

        $ciudadCliente = trim($direccionPrincipal->ciudad);

        $local = Local::with(['horarios', 'zonasEntrega'])
            ->where('id', $localId)
            ->where('activo', 1)
            ->where(function ($query) use ($ciudadCliente) {
                $query->whereRaw('LOWER(ciudad) = ?', [mb_strtolower($ciudadCliente)])
                    ->orWhereHas('zonasEntrega', function ($q) use ($ciudadCliente) {
                        $q->where('activo', 1)
                          ->whereRaw('LOWER(ciudad) = ?', [mb_strtolower($ciudadCliente)]);
                    });
            })
            ->firstOrFail();

        $productos = Producto::with([
                'categoria',
                'imagenPrincipal',
                'variantes' => $this->soloVariantesActivas(),
            ])
            ->where('local_id', $local->id)
            ->where('activo', 1)
            ->whereHas('variantes', function ($q) {
                $q->where('activo', 1); // sin variantes activas no se puede vender
            })
            ->latest()
            ->get();

        $this->prepararProductos($productos, $local->id);

        $favoritosIds = Favorito::where('cliente_id', Auth::id())
            ->pluck('producto_id')
            ->toArray();

        /* ----- Costo de envío ----- */

        // Hacia la dirección principal del cliente (es la que se muestra en el panel del local)
        // null = el local no entrega a domicilio o no cubre esa ciudad
        $costoEnvio = $this->costoEnvioA($local, $ciudadCliente);

        // Hacia cada dirección guardada del cliente (para el modal)
        $envios = Direccion::where('user_id', Auth::id())
            ->orderByDesc('principal')
            ->get()
            ->map(function ($d) use ($local) {
                $costo = $this->costoEnvioA($local, $d->ciudad);

                return [
                    'ciudad' => $d->ciudad,
                    'detalle' => $d->calle ?? $d->direccion ?? null,
                    'principal' => (bool) $d->principal,
                    'cubre' => $costo !== null,
                    'costo' => $costo,
                ];
            });

        // Zonas donde entrega este local (zonas activas + su propia ciudad).
        // Si el local no permite delivery, no hay zonas de entrega.
        $zonas = collect();

        if ($local->permite_delivery) {
            $zonas = $local->zonasEntrega
                ->where('activo', 1)
                ->map(fn ($z) => ['ciudad' => $z->ciudad, 'costo' => (float) $z->costo_envio])
                ->values();

            $ciudadLocal = mb_strtolower(trim((string) $local->ciudad));

            if ($ciudadLocal !== '' && !$zonas->contains(fn ($z) => mb_strtolower(trim($z['ciudad'])) === $ciudadLocal)) {
                $zonas->prepend(['ciudad' => $local->ciudad, 'costo' => (float) ($local->costo_envio ?? 0)]);
            }
        }

        return view('cliente.catalogo.local', compact(
            'local',
            'productos',
            'favoritosIds',
            'ciudadCliente',
            'costoEnvio',
            'envios',
            'zonas'
        ));
    }

    /* ---------------------------------------------------------------
     |  Detalle de producto
     * --------------------------------------------------------------- */

    public function producto($productoId)
    {
        $direccionPrincipal = Direccion::where('user_id', Auth::id())
            ->where('principal', 1)
            ->first();

        if (!$direccionPrincipal) {
            return redirect()
                ->route('cliente.catalogo.index')
                ->with('error', 'Agrega una dirección principal para ver productos disponibles.');
        }

        $ciudadCliente = trim($direccionPrincipal->ciudad);

        $producto = Producto::with([
                'local.zonasEntrega',
                'categoria',
                'imagenes',
                'imagenPrincipal',
                'variantes' => $this->soloVariantesActivas(),
            ])
            ->where('id', $productoId)
            ->where('activo', 1)
            ->whereHas('variantes', function ($q) {
                $q->where('activo', 1);
            })
            ->whereHas('local', function ($query) use ($ciudadCliente) {
                $query->where(function ($q) use ($ciudadCliente) {
                    $q->whereRaw('LOWER(ciudad) = ?', [mb_strtolower($ciudadCliente)])
                        ->orWhereHas('zonasEntrega', function ($z) use ($ciudadCliente) {
                            $z->where('activo', 1)
                              ->whereRaw('LOWER(ciudad) = ?', [mb_strtolower($ciudadCliente)]);
                        });
                });
            })
            ->firstOrFail();

        $this->prepararProductos(collect([$producto]), $producto->local_id);

        return view('cliente.catalogo.producto', compact('producto'));
    }
}