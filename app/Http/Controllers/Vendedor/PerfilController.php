<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('vendedor.perfil.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update([
            'name' => $request->name,
            'telefono' => $request->telefono,
        ]);

        return redirect()
            ->route('vendedor.perfil.index')
            ->with('success', 'Perfil actualizado correctamente.');
    }
}