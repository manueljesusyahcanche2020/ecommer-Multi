@extends('layouts.dashboard')

@section('title', 'Productos')
@section('page-title', 'Productos')
@section('page-subtitle', 'Administra los productos del local')

@section('content')

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">

            {{-- LOGO DEL LOCAL --}}
            @if($local->logo)
                <img
                    src="{{ asset('storage/' . $local->logo) }}"
                    alt="{{ $local->nombre }}"
                    style="width: 72px; height: 72px; object-fit: cover; border-radius: 18px; border: 1px solid #e5e7eb;"
                >
            @else
                <div style="width: 72px; height: 72px; border-radius: 18px; background: #fff7ed; display: flex; align-items: center; justify-content: center; border: 1px solid #fed7aa;">
                    <i class="fa-solid fa-store text-warning fs-3"></i>
                </div>
            @endif

            <div>
                <h4 class="fw-bold mb-1">
                    Productos de {{ $local->nombre }}
                </h4>

                <p class="text-muted mb-1">
                    Local seleccionado:
                    <strong class="text-dark">{{ $local->nombre }}</strong>
                </p>

                <small class="text-muted">
                    {{ $local->direccion ?? 'Sin dirección registrada' }}
                </small>

                <div class="mt-2">
                    <span class="badge bg-dark">
                        Productos registrados: {{ $productos->count() }}
                    </span>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('vendedor.dashboard.locales') }}" class="btn btn-outline-secondary fw-bold">
                <i class="fa-solid fa-arrow-left"></i>
                Volver
            </a>

            <a href="{{ route('vendedor.locales.productos.create', $local->id) }}" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-plus"></i>
                Nuevo producto
            </a>
        </div>
    </div>

    {{-- MENSAJE DE ÉXITO --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i>
            {{ session('success') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- MENSAJE DE ERROR --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            {{ session('error') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if($productos->count() > 0)

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Oferta</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($productos as $producto)
                        @php
                            $activas = $producto->variantes->where('activo', 1);

                            $precioMin = $activas->min('precio_venta');
                            $precioMax = $activas->max('precio_venta');

                            $ofertaMin = $activas->whereNotNull('precio_oferta')->min('precio_oferta');

                            $stockTotal = $activas->sum('stock');
                            $skuUnico   = $activas->count() === 1 ? $activas->first()->sku : null;
                        @endphp
                        <tr>
                            {{-- IMAGEN DEL PRODUCTO --}}
                            <td>
                                @if($producto->imagenPrincipal)
                                    <img
                                        src="{{ asset('storage/' . $producto->imagenPrincipal->imagen) }}"
                                        alt="{{ $producto->nombre }}"
                                        style="width: 72px; height: 72px; object-fit: cover; border-radius: 14px; border: 1px solid #e5e7eb;"
                                    >
                                @else
                                    <div style="width: 72px; height: 72px; border-radius: 14px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; border: 1px solid #e5e7eb;">
                                        <i class="fa-solid fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>

                            {{-- PRODUCTO --}}
                            <td>
                                <strong>{{ $producto->nombre }}</strong>

                                @if($producto->destacado)
                                    <span class="badge bg-warning text-dark ms-1">
                                        Destacado
                                    </span>
                                @endif

                                <br>

                                <small class="text-muted">
                                    {{ $producto->descripcion ?? 'Sin descripción' }}
                                </small>

                                @if($producto->sku)
                                    <br>
                                    <small class="text-muted">
                                        SKU: {{ $producto->sku }}
                                    </small>
                                @endif
                            </td>

                            {{-- CATEGORÍA --}}
                            <td>
                                @if($producto->categoria)
                                    <span class="badge bg-info text-dark">
                                        {{ $producto->categoria->nombre }}
                                    </span>
                                @else
                                    <span class="text-muted">Sin categoría</span>
                                @endif
                            </td>

                           {{-- PRECIO --}}
                            <td>
                                @if($activas->isEmpty())
                                    <span class="text-muted">—</span>
                                @elseif($precioMin == $precioMax)
                                    <strong>${{ number_format($precioMin, 2) }}</strong>
                                @else
                                    <strong>${{ number_format($precioMin, 2) }} – ${{ number_format($precioMax, 2) }}</strong>
                                @endif
                            </td>

                            {{-- OFERTA --}}
                            <td>
                                @if($ofertaMin)
                                    <span class="badge bg-warning text-dark">
                                        ${{ number_format($ofertaMin, 2) }}
                                    </span>
                                @else
                                    <span class="text-muted">Sin oferta</span>
                                @endif
                            </td>

                            {{-- STOCK --}}
                            <td>
                                <span class="badge bg-secondary">{{ $stockTotal }}</span>
                            </td>
                            {{-- ACTIVO --}}
                            <td>
                                @if($producto->activo)
                                    <span class="badge bg-success">
                                        Público
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        No público
                                    </span>
                                @endif
                            </td>

                            {{-- ACCIONES --}}
                            <td>
                                <div class="d-flex flex-wrap gap-2">

                                    <a href="{{ route('vendedor.locales.productos.edit', [$local->id, $producto->id]) }}"
                                       class="btn btn-sm btn-outline-dark fw-bold">
                                        <i class="fa-solid fa-pen"></i>
                                        Editar
                                    </a>

                                    <form method="POST"
                                          action="{{ route('vendedor.locales.productos.estado', [$local->id, $producto->id]) }}">
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
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @else

        <div class="text-center py-5">

            @if($local->logo)
                <img
                    src="{{ asset('storage/' . $local->logo) }}"
                    alt="{{ $local->nombre }}"
                    style="width: 90px; height: 90px; object-fit: cover; border-radius: 24px; border: 1px solid #e5e7eb;"
                    class="mb-3"
                >
            @else
                <i class="fa-solid fa-box fa-3x text-muted mb-3"></i>
            @endif

            <h5 class="fw-bold">
                {{ $local->nombre }} no tiene productos todavía
            </h5>

            <p class="text-muted">
                Productos registrados: 0
            </p>

            <a href="{{ route('vendedor.locales.productos.create', $local->id) }}" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-plus"></i>
                Crear producto
            </a>
        </div>

    @endif

</div>

@endsection