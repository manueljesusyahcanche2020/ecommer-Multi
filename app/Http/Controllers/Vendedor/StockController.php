<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\ProductoStock;
use App\Models\ProductoVariante;
use App\Models\Vendedor\Local;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $localId = $request->get('local_id');
        $estado = $request->get('estado');

        $locales = Local::where('vendedor_id', Auth::id())
            ->orderBy('nombre')
            ->get();

        $stocks = ProductoStock::with(['producto.imagenPrincipal', 'producto.categoria', 'local', 'variante'])
            ->whereHas('local', function ($query) {
                $query->where('vendedor_id', Auth::id());
            })
            ->when($buscar, function ($query) use ($buscar) {
                // `sku` ya no está en productos, está en producto_variantes
                $query->where(function ($w) use ($buscar) {
                    $w->whereHas('producto', function ($q) use ($buscar) {
                        $q->where('nombre', 'LIKE', "%{$buscar}%");
                    })->orWhereHas('variante', function ($q) use ($buscar) {
                        $q->where('sku', 'LIKE', "%{$buscar}%")
                          ->orWhere('nombre', 'LIKE', "%{$buscar}%");
                    });
                });
            })
            ->when($localId, function ($query) use ($localId) {
                $query->where('local_id', $localId);
            })
            ->when($estado === 'bajo', function ($query) {
                $query->whereColumn('stock', '<=', 'stock_minimo')
                      ->where('maneja_stock', 1);
            })
            ->when($estado === 'agotado', function ($query) {
                $query->where('stock', '<=', 0)
                      ->where('maneja_stock', 1);
            })
            ->latest()
            ->get();

        return view('vendedor.productos.stock.index', compact(
            'stocks',
            'locales',
            'buscar',
            'localId',
            'estado'
        ));
    }

    public function update(Request $request, ProductoStock $stock)
    {
        $stock->load('local');

        if (!$stock->local || $stock->local->vendedor_id !== Auth::id()) {
            abort(403, 'No tienes permiso para modificar este stock.');
        }

        $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'maneja_stock' => ['nullable'],
        ], [
            'stock.required' => 'El stock es obligatorio.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock no puede ser negativo.',
            'stock_minimo.integer' => 'El stock mínimo debe ser un número entero.',
        ]);

        DB::transaction(function () use ($request, $stock) {
            $stock->update([
                'stock' => $request->stock,
                'stock_minimo' => $request->stock_minimo ?? 0,
                'maneja_stock' => $request->boolean('maneja_stock'),
            ]);

            // Mantener sincronizado el stock que se muestra en las vistas de productos
            if ($stock->variante_id) {
                ProductoVariante::where('id', $stock->variante_id)
                    ->update(['stock' => $request->stock]);
            }
        });

        return redirect()
            ->route('vendedor.stock.index')
            ->with('success', 'Stock actualizado correctamente.');
    }
}