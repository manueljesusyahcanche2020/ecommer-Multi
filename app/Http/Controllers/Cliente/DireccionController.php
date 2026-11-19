<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Direccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DireccionController extends Controller
{
    public function index()
    {
        $direcciones = Direccion::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('cliente.direcciones.index', compact('direcciones'));
    }

    public function create()
    {
        return view('cliente.direcciones.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'calle' => ['required', 'string', 'max:191'],
            'numero' => ['nullable', 'string', 'max:50'],
            'cruzamientos' => ['nullable', 'string', 'max:191'],
            'colonia' => ['nullable', 'string', 'max:150'],
            'ciudad' => ['required', 'string', 'max:150'],
            'estado' => ['required', 'string', 'max:150'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],
            'referencia' => ['nullable', 'string', 'max:500'],
            'latitud' => ['nullable', 'numeric'],
            'longitud' => ['nullable', 'numeric'],
            'principal' => ['nullable'],
        ], [
            'nombre.required' => 'El nombre de quien recibe es obligatorio.',
            'calle.required' => 'La calle es obligatoria.',
            'ciudad.required' => 'La ciudad es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
        ]);

        try {
            $resultado = DB::select(
                'CALL crear_direccion_cliente(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    Auth::id(),
                    $this->limpiarTexto($request->nombre),
                    $this->limpiarTexto($request->telefono),
                    $this->limpiarTexto($request->calle),
                    $this->limpiarTexto($request->numero),
                    $this->limpiarTexto($request->cruzamientos),
                    $this->limpiarTexto($request->colonia),
                    $this->limpiarTexto($request->ciudad),
                    $this->limpiarTexto($request->estado),
                    $this->limpiarTexto($request->codigo_postal),
                    $this->limpiarTexto($request->referencia),
                    $request->latitud,
                    $request->longitud,
                    $request->boolean('principal') ? 1 : 0,
                ]
            );

            $mensaje = $resultado[0]->mensaje ?? 'Dirección guardada correctamente.';

            return redirect()
                ->route('cliente.direcciones.index')
                ->with('success', $mensaje);

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $direccion = Direccion::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('cliente.direcciones.show', compact('direccion'));
    }

    public function edit($id)
    {
        $direccion = Direccion::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('cliente.direcciones.edit', compact('direccion'));
    }

    public function update(Request $request, $id)
    {
        $direccion = Direccion::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'calle' => ['required', 'string', 'max:191'],
            'numero' => ['nullable', 'string', 'max:50'],
            'cruzamientos' => ['nullable', 'string', 'max:191'],
            'colonia' => ['nullable', 'string', 'max:150'],
            'ciudad' => ['required', 'string', 'max:150'],
            'estado' => ['required', 'string', 'max:150'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],
            'referencia' => ['nullable', 'string', 'max:500'],
            'latitud' => ['nullable', 'numeric'],
            'longitud' => ['nullable', 'numeric'],
            'principal' => ['nullable'],
        ], [
            'nombre.required' => 'El nombre de quien recibe es obligatorio.',
            'calle.required' => 'La calle es obligatoria.',
            'ciudad.required' => 'La ciudad es obligatoria.',
            'estado.required' => 'El estado es obligatorio.',
        ]);

        try {
            $resultado = DB::select(
                'CALL actualizar_direccion_cliente(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $direccion->id,
                    Auth::id(),
                    $this->limpiarTexto($request->nombre),
                    $this->limpiarTexto($request->telefono),
                    $this->limpiarTexto($request->calle),
                    $this->limpiarTexto($request->numero),
                    $this->limpiarTexto($request->cruzamientos),
                    $this->limpiarTexto($request->colonia),
                    $this->limpiarTexto($request->ciudad),
                    $this->limpiarTexto($request->estado),
                    $this->limpiarTexto($request->codigo_postal),
                    $this->limpiarTexto($request->referencia),
                    $request->latitud,
                    $request->longitud,
                    $request->boolean('principal') ? 1 : 0,
                ]
            );

            $mensaje = $resultado[0]->mensaje ?? 'Dirección actualizada correctamente.';

            return redirect()
                ->route('cliente.direcciones.index')
                ->with('success', $mensaje);

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $resultado = DB::select(
                'CALL eliminar_direccion_cliente(?, ?)',
                [
                    $id,
                    Auth::id(),
                ]
            );

            $mensaje = $resultado[0]->mensaje ?? 'Dirección eliminada correctamente.';

            return redirect()
                ->route('cliente.direcciones.index')
                ->with('success', $mensaje);

        } catch (\Throwable $e) {
            return redirect()
                ->route('cliente.direcciones.index')
                ->with('error', $e->getMessage());
        }
    }

    private function limpiarTexto($valor)
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim($valor);

        // Quita etiquetas HTML y scripts
        $valor = strip_tags($valor);

        // Quita caracteres invisibles/control
        $valor = preg_replace('/[\x00-\x1F\x7F]/u', '', $valor);

        return $valor;
    }
}