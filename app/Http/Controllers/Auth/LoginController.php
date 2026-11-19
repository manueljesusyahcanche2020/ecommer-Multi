<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function index()
    {
        return view('auth.login');
    }

    /**
     * Procesar login
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Validar si el usuario está bloqueado o pendiente
            if ($user->estado !== 'activo') {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Tu cuenta no está activa.',
                ])->onlyInput('email');
            }

            // Redirección según rol
            if ($user->role_id == 1) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->role_id == 2) {
                return redirect()->route('vendedor.dashboard');
            }

            if ($user->role_id == 3) {
                return redirect()->route('cliente.dashboard');
            }

            Auth::logout();

            return back()->withErrors([
                'email' => 'Tu rol no tiene acceso al sistema.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'El correo o la contraseña no son correctos.',
        ])->onlyInput('email');
    }
}