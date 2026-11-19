<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;

class AdminPedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with(['cliente', 'local'])
            ->latest()
            ->get();

        return view('admin.pedidos.index', compact('pedidos'));
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
            ->firstOrFail();

        return view('admin.pedidos.show', compact('pedido'));
    }
}