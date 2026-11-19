<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPedidos = Pedido::where('cliente_id', Auth::id())->count();

        $pedidosPendientes = Pedido::where('cliente_id', Auth::id())
            ->whereIn('estado_pedido', ['pendiente', 'aceptado', 'preparando', 'en_camino'])
            ->count();

        $ultimosPedidos = Pedido::with('local')
            ->where('cliente_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();

        return view('cliente.dashboard', compact(
            'totalPedidos',
            'pedidosPendientes',
            'ultimosPedidos'
        ));
    }
}