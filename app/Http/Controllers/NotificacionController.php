<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    public function index()
    {
        $notificaciones = Notificacion::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('notificaciones.index', compact('notificaciones'));
    }

    public function marcarLeida($id)
    {
        $notificacion = Notificacion::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notificacion->update([
            'leida' => 1,
        ]);

        if ($notificacion->url) {
            return redirect($notificacion->url);
        }

        return back();
    }
    public function contador()
{
    $total = Notificacion::where('user_id', Auth::id())
        ->where('leida', 0)
        ->count();

    return response()->json([
        'total' => $total,
    ]);
}
}