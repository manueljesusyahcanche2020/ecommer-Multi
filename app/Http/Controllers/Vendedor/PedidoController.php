<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Notificacion;
use App\Models\Direccion;
use App\Models\Vendedor\Local;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductoStock;
use Illuminate\Support\Facades\DB;
class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $estado = $request->get('estado');
        $localId = $request->get('local_id');

        $locales = Local::where('vendedor_id', Auth::id())
            ->orderBy('nombre')
            ->get();

        $pedidos = Pedido::with(['cliente', 'local'])
            ->whereHas('local', function ($query) {
                $query->where('vendedor_id', Auth::id());
            })
            ->when($buscar, function ($query) use ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('codigo', 'LIKE', "%{$buscar}%")
                      ->orWhereHas('cliente', function ($cliente) use ($buscar) {
                          $cliente->where('name', 'LIKE', "%{$buscar}%")
                                  ->orWhere('telefono', 'LIKE', "%{$buscar}%");
                      });
                });
            })
            ->when($estado, function ($query) use ($estado) {
                $query->where('estado_pedido', $estado);
            })
            ->when($localId, function ($query) use ($localId) {
                $query->where('local_id', $localId);
            })
            ->latest()
            ->get();

        return view('vendedor.pedidos.index', compact(
            'pedidos',
            'locales',
            'buscar',
            'estado',
            'localId'
        ));
    }

    public function show($pedidoId)
    {
        $pedido = Pedido::with([
                'cliente',
                'local',
                'direccion',
                'detalles',
            ])
            ->where('id', $pedidoId)
            ->whereHas('local', function ($query) {
                $query->where('vendedor_id', Auth::id());
            })
            ->firstOrFail();

        return view('vendedor.pedidos.show', compact('pedido'));
    }
public function cambiarEstado(Request $request, $pedidoId)
{
    $request->validate([
        'estado_pedido' => [
            'required',
            'in:pendiente,aceptado,preparando,en_camino,entregado,cancelado'
        ],
        'estado_pago' => [
            'required',
            'in:pendiente,pagado,fallido,reembolsado'
        ],
        'notas_vendedor' => ['nullable', 'string', 'max:1000'],
    ]);

    $pedido = Pedido::with(['detalles', 'local'])
        ->where('id', $pedidoId)
        ->whereHas('local', function ($query) {
            $query->where('vendedor_id', Auth::id());
        })
        ->firstOrFail();

    $estadoAnterior = $pedido->estado_pedido;
    $estadoNuevo = $request->estado_pedido;
    $estadoPagoNuevo = $request->estado_pago;

    if (in_array($estadoAnterior, ['cancelado', 'entregado'])) {
        return back()->with('error', 'Este pedido ya no se puede modificar.');
    }

    if ($estadoNuevo === 'pendiente' && $estadoAnterior !== 'pendiente') {
        return back()->with('error', 'No puedes regresar un pedido a pendiente.');
    }

    // 🛑 RESTRICCIÓN: No se puede entregar hasta que esté pagado
    if ($estadoNuevo === 'entregado' && $estadoPagoNuevo !== 'pagado') {
        return back()->with('error', 'No se puede marcar el pedido como entregado hasta que el estado de pago sea "Pagado".');
    }

    /*
     * Si cancela, se devuelve stock y se notifica al cliente.
     */
    if ($estadoNuevo === 'cancelado') {
        try {
            DB::transaction(function () use ($pedido, $request, $estadoPagoNuevo) {

                foreach ($pedido->detalles as $detalle) {
                    $stock = ProductoStock::where('producto_id', $detalle->producto_id)
                        ->where('variante_id', $detalle->variante_id)
                        ->lockForUpdate()
                        ->first();

                    if ($stock && $stock->maneja_stock) {
                        $stock->increment('stock', $detalle->cantidad);
                    }
                }

                $nota = $request->notas_vendedor ?: 'Pedido cancelado por el vendedor.';

                $pedido->update([
                    'estado_pedido' => 'cancelado',
                    'estado_pago' => $estadoPagoNuevo,
                    'notas_vendedor' => $nota,
                    'fecha_cancelado' => now(),
                ]);

                Notificacion::create([
                    'user_id' => $pedido->cliente_id,
                    'titulo' => 'Pedido cancelado',
                    'mensaje' => 'Tu pedido ' . $pedido->codigo . ' fue cancelado. ' . $nota,
                    'tipo' => 'cancelacion',
                    'url' => route('cliente.pedidos.show', $pedido->id),
                    'leida' => 0,
                ]);
            });

            return redirect()
                ->route('vendedor.pedidos.show', $pedido->id)
                ->with('success', 'Pedido cancelado correctamente, stock devuelto y cliente notificado.');

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /*
     * Cambios normales de estado.
     */
    $datos = [
        'estado_pedido' => $estadoNuevo,
        'estado_pago' => $estadoPagoNuevo,
        'notas_vendedor' => $request->notas_vendedor,
    ];

    if ($estadoNuevo === 'aceptado' && !$pedido->fecha_aceptado) {
        $datos['fecha_aceptado'] = now();
    }

    if ($estadoNuevo === 'entregado') {
        $datos['fecha_entregado'] = now();
    }

    DB::transaction(function () use ($pedido, $datos, $estadoNuevo, $estadoAnterior, $request) {
        $pedido->update($datos);

        if ($estadoNuevo !== $estadoAnterior) {
            $estadoTexto = [
                'pendiente' => 'Pendiente',
                'aceptado' => 'Aceptado',
                'preparando' => 'Preparando',
                'en_camino' => 'En camino',
                'entregado' => 'Entregado',
                'cancelado' => 'Cancelado',
            ][$estadoNuevo] ?? $estadoNuevo;

            $mensaje = 'Tu pedido ' . $pedido->codigo . ' ahora está: ' . $estadoTexto . '.';

            if ($request->filled('notas_vendedor')) {
                $mensaje .= ' Nota del vendedor: ' . $request->notas_vendedor;
            }

            Notificacion::create([
                'user_id' => $pedido->cliente_id,
                'titulo' => 'Tu pedido cambió de estado',
                'mensaje' => $mensaje,
                'tipo' => 'estado',
                'url' => route('cliente.pedidos.show', $pedido->id),
                'leida' => 0,
            ]);
        }
    });

    return redirect()
        ->route('vendedor.pedidos.show', $pedido->id)
        ->with('success', 'Estado del pedido y pago actualizados correctamente.');
}
public function ticket($id)
{
    $pedido = Pedido::with([
        'cliente',
        'local',
        'direccion',
        'detalles'
    ])->findOrFail($id);

    return view('vendedor.pedidos.ticket', compact('pedido'));
}
public function porEnviar()
{
    $vendedorId = auth()->id();

    $pedidos = Pedido::with([
            'cliente',
            'local',
            'direccion',
            'detalles'
        ])
        ->whereHas('local', function ($query) use ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        })
        ->where('tipo_entrega', 'delivery')
        ->whereIn('estado_pedido', ['preparando', 'en_camino'])
        ->orderBy('created_at', 'asc')
        ->get();

    return view('vendedor.pedidos.enviar', compact('pedidos'));
}
}