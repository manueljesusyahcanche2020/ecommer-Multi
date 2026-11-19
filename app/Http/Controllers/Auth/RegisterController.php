<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Mostrar formulario de registro
     */
    public function index()
    {
        return view('auth.register');
    }

    /**
     * Guardar nuevo usuario
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
            ],
            'email' => [
                'required',
                'email',
                'max:191',
                'unique:users,email',
            ],
            'telefono' => [
                'nullable',
                'string',
                'max:20',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
            'tipo_cuenta' => [
                'required',
                'in:cliente,vendedor',
            ],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no debe pasar de 191 caracteres.',

            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo válido.',
            'email.unique' => 'Este correo ya está registrado.',

            'telefono.max' => 'El teléfono no debe pasar de 20 caracteres.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener mínimo 8 caracteres.',

            'tipo_cuenta.required' => 'Selecciona el tipo de cuenta.',
            'tipo_cuenta.in' => 'El tipo de cuenta no es válido.',
        ]);

        /*
         * Roles:
         * 1 = Admin
         * 2 = Vendedor
         * 3 = Cliente
         */
        $roleId = $request->tipo_cuenta === 'vendedor' ? 2 : 3;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->password),
            'role_id' => $roleId,
            'estado' => 'activo',
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        if ($user->role_id == 2) {
            return redirect()->route('vendedor.dashboard')
                ->with('success', 'Cuenta de vendedor creada correctamente.');
        }

        return redirect()->route('cliente.dashboard')
            ->with('success', 'Cuenta creada correctamente.');
    }
}