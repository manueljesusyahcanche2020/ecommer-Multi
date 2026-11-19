<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Favorito;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class FavoritoController extends Controller
{
  public function toggle($productoId)
    {
        $resultado = DB::select('CALL toggle_favorito(?, ?)', [
            Auth::id(),
            $productoId
        ]);

        $mensaje = $resultado[0]->mensaje ?? 'Favorito actualizado.';

        return back()->with('success', $mensaje);
    }
        public function index()
    {
        $favoritos = Favorito::with([
                'producto.local',
                'producto.categoria',
                'producto.stock',
                'producto.imagenPrincipal',
            ])
            ->where('cliente_id', Auth::id())
            ->latest()
            ->get();

        return view('cliente.favoritos.index', compact('favoritos'));
    }
}