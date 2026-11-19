<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminUsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with('role')
            ->latest()
            ->get();

        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function cambiarEstado($usuarioId)
    {
        $usuario = User::findOrFail($usuarioId);

        $usuario->update([
            'activo' => !$usuario->activo,
        ]);

        $mensaje = $usuario->activo
            ? 'Usuario activado correctamente.'
            : 'Usuario desactivado correctamente.';

        return back()->with('success', $mensaje);
    }
}