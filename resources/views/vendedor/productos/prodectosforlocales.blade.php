@extends('layouts.dashboard')

@section('title', 'Mis productos')
@section('page-title', 'Mis productos')
@section('page-subtitle', 'Todos los productos de tus locales')

@section('content')

<style>
    .productos-scroll {
        max-height: 680px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 8px;
    }

    .productos-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .productos-scroll::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 20px;
    }

    .producto-card {
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        height: 100%;
        transition: all .2s ease;
    }

    .producto-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 34px rgba(15, 23, 42, .10);
    }

    .producto-img {
        width: 100%;
        height: 170px;
        object-fit: cover;
        background: #f3f4f6;
    }

    .producto-img-empty {
        width: 100%;
        height: 170px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 36px;
    }

    .producto-body {
        padding: 16px;
    }

    .producto-title {
        font-weight: 800;
        margin-bottom: 4px;
        color: #111827;
    }

    .producto-desc {
        color: #6b7280;
        font-size: 14px;
        min-height: 42px;
    }

    .filter-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .local-chip {
        display: inline-block;
        padding: 5px 10px;
        background: #fff7ed;
        color: #9a3412;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    @media (max-width: 768px) {
        .productos-scroll {
            max-height: none;
            overflow: visible;
        }
    }
</style>

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Todos mis productos</h4>
            <p class="text-muted mb-0">
                Productos encontrados:
                <span class="badge bg-dark">{{ $productos->count() }}</span>
            </p>
        </div>

        <a href="{{ route('vendedor.dashboard.locales') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-store"></i>
            Mis locales
        </a>
    </div>

    {{-- FILTROS --}}
    <form method="GET" action="{{ route('vendedor.productos.todos') }}" class="filter-card">
        <div class="row g-3 align-items-end">

            <div class="col-md-4">
                <label class="form-label fw-bold">Buscar producto</label>
                <input
                    type="text"
                    name="buscar"
                    class="form-control"
                    placeholder="Nombre, descripción o SKU"
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
                    <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>
                        Públicos
                    </option>
                    <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>
                        No públicos
                    </option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-warning text-white fw-bold w-100">
                    <i class="fa-solid fa-filter"></i>
                    Filtrar
                </button>
            </div>

            <div class="col-12">
                <a href="{{ route('vendedor.productos.todos') }}" class="btn btn-sm btn-outline-secondary fw-bold">
                    Limpiar filtros
                </a>
            </div>

        </div>
    </form>

    {{-- MENSAJES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if($productos->count() > 0)

        <div class="productos-scroll">
            <div class="row g-4">

                @foreach($productos as $producto)
                    @php
                        // Precio, oferta, stock y SKU viven en producto_variantes
                        $activas = $producto->variantes->where('activo', 1);

                        $precioMin  = $activas->min('precio_venta');
                        $precioMax  = $activas->max('precio_venta');
                        $ofertaMin  = $activas->whereNotNull('precio_oferta')->min('precio_oferta');
                        $stockTotal = $activas->sum('stock');
                        $skuUnico   = $activas->count() === 1 ? $activas->first()->sku : null;
                    @endphp

                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="producto-card">

                            @if($producto->imagenPrincipal)
                                <img
                                    src="{{ asset('storage/' . $producto->imagenPrincipal->imagen) }}"
                                    alt="{{ $producto->nombre }}"
                                    class="producto-img"
                                >
                            @else
                                <div class="producto-img-empty">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            @endif

                            <div class="producto-body">

                                <span class="local-chip">
                                    <i class="fa-solid fa-store"></i>
                                    {{ $producto->local->nombre ?? 'Sin local' }}
                                </span>

                                <h5 class="producto-title">
                                    {{ $producto->nombre }}
                                </h5>

                                <p class="producto-desc">
                                    {{ $producto->descripcion ?? 'Sin descripción' }}
                                    @if($skuUnico)
                                        <br>
                                        <small class="text-muted">SKU: {{ $skuUnico }}</small>
                                    @endif
                                </p>

                                <div class="mb-2">
                                    @if($producto->categoria)
                                        <span class="badge bg-info text-dark">
                                            {{ $producto->categoria->nombre }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark">
                                            Sin categoría
                                        </span>
                                    @endif

                                    @if($producto->destacado)
                                        <span class="badge bg-warning text-dark">
                                            Destacado
                                        </span>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <small class="text-muted">Precio</small>
                                        <h5 class="fw-bold mb-0">
                                            @if($activas->isEmpty())
                                                —
                                            @elseif($precioMin == $precioMax)
                                                ${{ number_format($precioMin, 2) }}
                                            @else
                                                ${{ number_format($precioMin, 2) }} – ${{ number_format($precioMax, 2) }}
                                            @endif
                                        </h5>
                                    </div>

                                    <div class="text-end">
                                        <small class="text-muted">Stock</small>
                                        <h5 class="fw-bold mb-0">
                                            {{ $stockTotal }}
                                        </h5>
                                    </div>
                                </div>

                                @if($ofertaMin)
                                    <div class="mb-2">
                                        <span class="badge bg-warning text-dark">
                                            Oferta: ${{ number_format($ofertaMin, 2) }}
                                        </span>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    @if($producto->activo)
                                        <span class="badge bg-success">
                                            Público
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            No público
                                        </span>
                                    @endif
                                </div>

                                <div class="d-flex flex-wrap gap-2">

                                    <a href="{{ route('vendedor.locales.productos.edit', [$producto->local_id, $producto->id]) }}"
                                       class="btn btn-sm btn-outline-dark fw-bold">
                                        <i class="fa-solid fa-pen"></i>
                                        Editar
                                    </a>

                                    <form method="POST"
                                          action="{{ route('vendedor.locales.productos.estado', [$producto->local_id, $producto->id]) }}">
                                        @csrf
                                        @method('PATCH')

                                        @if($producto->activo)
                                            <button type="submit" class="btn btn-sm btn-outline-warning fw-bold">
                                                <i class="fa-solid fa-pause"></i>
                                                No público
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-sm btn-success fw-bold">
                                                <i class="fa-solid fa-check"></i>
                                                Publicar
                                            </button>
                                        @endif
                                    </form>

                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>

    @else

        <div class="text-center py-5">
            <i class="fa-solid fa-box-open fa-3x text-muted mb-3"></i>

            <h5 class="fw-bold">No se encontraron productos</h5>

            <p class="text-muted">
                Puedes crear productos desde la sección de locales.
            </p>

            <a href="{{ route('vendedor.dashboard.locales') }}" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-store"></i>
                Ir a mis locales
            </a>
        </div>

    @endif

</div>

@endsection
