<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Vendedor\Local;
use App\Models\Vendedor\LocalZonaEntrega;
use Illuminate\Support\Facades\Auth;

class LocalZonaEntregaController extends Controller
{
    public function cambiarEstado($localId, $zonaId)
    {
        $local = Local::where('id', $localId)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        $zona = LocalZonaEntrega::where('id', $zonaId)
            ->where('local_id', $local->id)
            ->firstOrFail();

        $zona->update([
            'activo' => !$zona->activo,
        ]);

        $mensaje = $zona->activo
            ? 'Zona de entrega activada correctamente.'
            : 'Zona de entrega desactivada correctamente.';

        return redirect()
            ->route('vendedor.locales.show', $local->id)
            ->with('success', $mensaje);
    }

    public function destroy($localId, $zonaId)
    {
        $local = Local::where('id', $localId)
            ->where('vendedor_id', Auth::id())
            ->firstOrFail();

        $zona = LocalZonaEntrega::where('id', $zonaId)
            ->where('local_id', $local->id)
            ->firstOrFail();

        $zona->delete();

        return redirect()
            ->route('vendedor.locales.show', $local->id)
            ->with('success', 'Zona de entrega eliminada correctamente.');
    }
}