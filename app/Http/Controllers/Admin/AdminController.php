<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Vendedor\Local;

class AdminController extends Controller
{
    public function index()
    {
        $totalUsuarios = User::count();
        $totalLocales = Local::count();
        $totalProductos = Producto::count();
        $totalPedidos = Pedido::count();

        $pedidosPendientes = Pedido::where('estado_pedido', 'pendiente')->count();
        $pedidosCancelados = Pedido::where('estado_pedido', 'cancelado')->count();
        $pedidosEntregados = Pedido::where('estado_pedido', 'entregado')->count();

        $ventasTotales = Pedido::where('estado_pedido', 'entregado')
            ->sum('total');

        return view('admin.dashboard', compact(
            'totalUsuarios',
            'totalLocales',
            'totalProductos',
            'totalPedidos',
            'pedidosPendientes',
            'pedidosCancelados',
            'pedidosEntregados',
            'ventasTotales'
        ));
    }
}