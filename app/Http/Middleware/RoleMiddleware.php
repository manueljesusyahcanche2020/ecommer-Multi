<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()
                ->route('login')
                ->with('error', 'Debes iniciar sesión para acceder.');
        }

        $user = Auth::user();

        // Si el usuario es Administrador (ID 1), tiene acceso total (opcional)
        if ((int) $user->role_id === 1) {
            return $next($request);
        }

        $rolesPermitidos = [
            'admin'    => 1,
            'vendedor' => 2,
            'cliente'  => 3,
        ];

        foreach ($roles as $rol) {
            if (isset($rolesPermitidos[$rol]) && (int) $user->role_id === (int) $rolesPermitidos[$rol]) {
                return $next($request);
            }
        }

        abort(403, 'No tienes los permisos requeridos para acceder a esta página.');
    }
}