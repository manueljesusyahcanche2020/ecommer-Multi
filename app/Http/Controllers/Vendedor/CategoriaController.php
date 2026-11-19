<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Local;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::with('local')
            ->whereHas('local', function ($query) {
                $query->where('vendedor_id', Auth::id());
            })
            ->latest()
            ->paginate(10);

        return view('vendedor.categorias.index', compact('categorias'));
    }

    public function create()
    {
        $locales = Local::where('vendedor_id', Auth::id())
            ->where('activo', 1)
            ->get();

        return view('vendedor.categorias.create', compact('locales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'local_id' => ['required', 'exists:locales,id'],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $this->validarLocalId($request->local_id);

        Categoria::create([
            'local_id' => $request->local_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'orden' => $request->orden ?? 0,
            'activo' => $request->boolean('activo', true),
        ]);

        return redirect()
            ->route('vendedor.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria)
    {
        $this->validarCategoria($categoria);

        $locales = Local::where('vendedor_id', Auth::id())
            ->where('activo', 1)
            ->get();

        return view('vendedor.categorias.edit', compact('categoria', 'locales'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $this->validarCategoria($categoria);

        $request->validate([
            'local_id' => ['required', 'exists:locales,id'],
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'orden' => ['nullable', 'integer', 'min:0'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $this->validarLocalId($request->local_id);

        $categoria->update([
            'local_id' => $request->local_id,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'orden' => $request->orden ?? 0,
            'activo' => $request->boolean('activo'),
        ]);

        return redirect()
            ->route('vendedor.categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria)
    {
        $this->validarCategoria($categoria);
        $categoria->delete();

        return redirect()
            ->route('vendedor.categorias.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }

    private function validarLocalId($localId)
    {
        $existe = Local::where('id', $localId)
            ->where('vendedor_id', Auth::id())
            ->exists();

        if (!$existe) {
            abort(403, 'No tienes permiso para usar este local.');
        }
    }

    private function validarCategoria(Categoria $categoria)
    {
        $categoria->load('local');

        if (!$categoria->local || $categoria->local->vendedor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para acceder a esta categoría.');
        }
    }
}