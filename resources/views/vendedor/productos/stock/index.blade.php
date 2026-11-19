@extends('layouts.dashboard')

@section('title', 'Stock')
@section('page-title', 'Stock')
@section('page-subtitle', 'Controla el inventario de tus productos')

@section('content')

<style>
    .stock-img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
    }

    .stock-img-empty {
        width: 64px;
        height: 64px;
        border-radius: 14px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
    }

    .filter-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .stock-input {
        max-width: 100px;
    }

    .stock-scroll {
        max-height: 680px;
        overflow-y: auto;
        overflow-x: auto;
        padding-right: 6px;
    }

    .stock-scroll::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .stock-scroll::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 20px;
    }
</style>

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Inventario</h4>
            <p class="text-muted mb-0">
                Registros encontrados:
                <span class="badge bg-dark">{{ $stocks->count() }}</span>
            </p>
        </div>

        <a href="{{ route('vendedor.productos.todos') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-box"></i>
            Ver productos
        </a>
    </div>

    {{-- MENSAJE DE ÉXITO --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i>
            {{ session('success') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- ERRORES --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Revisa los datos:</strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- FILTROS --}}
    <form method="GET" action="{{ route('vendedor.stock.index') }}" class="filter-card">
        <div class="row g-3 align-items-end">

            <div class="col-md-4">
                <label class="form-label fw-bold">Buscar producto</label>
                <input
                    type="text"
                    name="buscar"
                    class="form-control"
                    placeholder="Nombre o SKU"
                    value="{{ request('buscar') }}"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Local</label>
                <select name="local_id" class="form-control">
                    <option value="">Todos los locales</option>

                    @foreach($locales as $local)
                        <option value="{{ $local->id }}" {{ request('local_id') == $local->id ? 'selected' : '' }}>
                            {{ $local->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Estado</label>
                <select name="estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="bajo" {{ request('estado') == 'bajo' ? 'selected' : '' }}>
                        Stock bajo
                    </option>
                    <option value="agotado" {{ request('estado') == 'agotado' ? 'selected' : '' }}>
                        Agotados
                    </option>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-warning text-white fw-bold w-100">
                    <i class="fa-solid fa-filter"></i>
                    Filtrar
                </button>
            </div>

            <div class="col-12">
                <a href="{{ route('vendedor.stock.index') }}" class="btn btn-sm btn-outline-secondary fw-bold">
                    Limpiar filtros
                </a>
            </div>

        </div>
    </form>

    @if($stocks->count() > 0)

        <div class="stock-scroll">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Local</th>
                        <th>Variante</th>
                        <th>Stock</th>
                        <th>Mínimo</th>
                        <th>Estado</th>
                        <th>Actualizar</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($stocks as $stock)
                        <tr>
                            {{-- IMAGEN --}}
                            <td>
                                @if($stock->producto && $stock->producto->imagenPrincipal)
                                    <img
                                        src="{{ asset('storage/' . $stock->producto->imagenPrincipal->imagen) }}"
                                        alt="{{ $stock->producto->nombre }}"
                                        class="stock-img"
                                    >
                                @else
                                    <div class="stock-img-empty">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                @endif
                            </td>

                            {{-- PRODUCTO --}}
                            <td>
                                <strong>{{ $stock->producto->nombre ?? 'Producto eliminado' }}</strong>

                                @if($stock->variante?->sku)
                                    <br>
                                    <small class="text-muted">SKU: {{ $stock->variante->sku }}</small>
                                @endif

                                @if($stock->producto?->categoria)
                                    <br>
                                    <span class="badge bg-info text-dark">
                                        {{ $stock->producto->categoria->nombre }}
                                    </span>
                                @endif
                            </td>

                            {{-- LOCAL --}}
                            <td>
                                {{ $stock->local->nombre ?? 'Sin local' }}
                            </td>

                            {{-- VARIANTE --}}
                            <td>
                                @if($stock->variante)
                                    <span class="badge bg-light text-dark">
                                        {{ $stock->variante->nombre }}{{ $stock->variante->valor ? ': ' . $stock->variante->valor : '' }}
                                    </span>
                                @else
                                    <span class="text-muted">Sin variante</span>
                                @endif
                            </td>

                            {{-- STOCK --}}
                            <td>
                                <input
                                    type="number"
                                    name="stock"
                                    form="stock-{{ $stock->id }}"
                                    class="form-control stock-input"
                                    value="{{ $stock->stock }}"
                                    min="0"
                                >
                            </td>

                            {{-- MÍNIMO --}}
                            <td>
                                <input
                                    type="number"
                                    name="stock_minimo"
                                    form="stock-{{ $stock->id }}"
                                    class="form-control stock-input"
                                    value="{{ $stock->stock_minimo }}"
                                    min="0"
                                >
                            </td>

                            {{-- ESTADO --}}
                            <td>
                                @if(!$stock->maneja_stock)
                                    <span class="badge bg-secondary">Sin control</span>
                                @elseif($stock->stock <= 0)
                                    <span class="badge bg-danger">Agotado</span>
                                @elseif($stock->stock <= $stock->stock_minimo)
                                    <span class="badge bg-warning text-dark">Stock bajo</span>
                                @else
                                    <span class="badge bg-success">Disponible</span>
                                @endif

                                <br>

                                <label class="form-check mt-2">
                                    <input
                                        type="checkbox"
                                        name="maneja_stock"
                                        value="1"
                                        form="stock-{{ $stock->id }}"
                                        class="form-check-input"
                                        {{ $stock->maneja_stock ? 'checked' : '' }}
                                    >
                                    <span class="form-check-label small">
                                        Manejar
                                    </span>
                                </label>
                            </td>

                            {{-- GUARDAR --}}
                            <td>
                                <form id="stock-{{ $stock->id }}"
                                      method="POST"
                                      action="{{ route('vendedor.stock.update', $stock->id) }}">
                                    @csrf
                                    @method('PUT')

                                    <button type="submit" class="btn btn-sm btn-warning text-white fw-bold">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Guardar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @else

        <div class="text-center py-5">
            <i class="fa-solid fa-boxes-stacked fa-3x text-muted mb-3"></i>

            <h5 class="fw-bold">No hay registros de stock</h5>

            <p class="text-muted">
                Cuando crees productos, aparecerá su stock aquí.
            </p>

            <a href="{{ route('vendedor.dashboard.locales') }}" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-store"></i>
                Ir a mis locales
            </a>
        </div>

    @endif

</div>

@endsection