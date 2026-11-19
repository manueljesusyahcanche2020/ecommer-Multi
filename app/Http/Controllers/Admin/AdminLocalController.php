<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendedor\Local;
use App\Models\Producto;

class AdminLocalController extends Controller
{
    public function index()
    {
        $locales = Local::with('vendedor')
            ->latest()
            ->get();

        return view('admin.locales.index', compact('locales'));
    }

    public function cambiarEstado($localId)
    {
        $local = Local::findOrFail($localId);

        $nuevoEstado = !$local->activo;

        $local->update([
            'activo' => $nuevoEstado,
            'estado_local' => $nuevoEstado ? 'cerrado' : 'cerrado',
        ]);

        if (!$nuevoEstado) {
            Producto::where('local_id', $local->id)
                ->update(['activo' => 0]);
        }

        $mensaje = $nuevoEstado
            ? 'Local activado correctamente.'
            : 'Local desactivado correctamente. Sus productos quedaron ocultos.';

        return back()->with('success', $mensaje);
    }
}