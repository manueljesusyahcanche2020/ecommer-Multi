<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Local;
use App\Models\Producto;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $vendedorId = Auth::id();

        $totalLocales = Local::where('vendedor_id', $vendedorId)->count();

        $totalProductos = Producto::whereHas('local', function ($query) use ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        })->count();

        $pedidosPendientes = Pedido::whereHas('local', function ($query) use ($vendedorId) {
            $query->where('vendedor_id', $vendedorId);
        })
        ->where('estado_pedido', 'pendiente')
        ->count();

        $ultimosPedidos = Pedido::with(['local'])
            ->whereHas('local', function ($query) use ($vendedorId) {
                $query->where('vendedor_id', $vendedorId);
            })
            ->latest()
            ->take(5)
            ->get();

        return view('vendedor.dashboard', compact(
            'totalLocales',
            'totalProductos',
            'pedidosPendientes',
            'ultimosPedidos'
        ));
    }
}