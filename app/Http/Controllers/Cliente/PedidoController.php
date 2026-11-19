<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoDetallles;
use App\Models\ProductoStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with('local')
            ->where('cliente_id', Auth::id())
            ->latest()
            ->get();

        return view('cliente.pedidos.index', compact('pedidos'));
    }

    public function show($pedidoId)
    {
        $pedido = Pedido::with(['local', 'detalles', 'direccion'])
            ->where('id', $pedidoId)
            ->where('cliente_id', Auth::id())
            ->firstOrFail();

        return view('cliente.pedidos.show', compact('pedido'));
    }

    public function cancelar($pedidoId)
    {
        try {
            $resultado = DB::select('CALL cancelar_pedido_cliente(?, ?)', [
                $pedidoId,
                Auth::id()
            ]);

            $mensaje = $resultado[0]->mensaje ?? 'Pedido cancelado correctamente.';

            return redirect()
                ->route('cliente.pedidos.show', $pedidoId)
                ->with('success', $mensaje);

        } catch (\Throwable $e) {
            return redirect()
                ->route('cliente.pedidos.show', $pedidoId)
                ->with('error', $e->getMessage());
        }
    }
}