@extends('layouts.dashboard')

@section('title', 'Mis favoritos')
@section('page-title', 'Mis favoritos')
@section('page-subtitle', 'Productos que guardaste como favoritos')

@section('content')

<style>
    .favorito-card {
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        background: white;
        overflow: hidden;
        height: 100%;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        transition: all .2s ease;
    }

    .favorito-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 35px rgba(15, 23, 42, .10);
    }

    .favorito-img {
        width: 100%;
        height: 175px;
        object-fit: cover;
        background: #f3f4f6;
    }

    .favorito-img-empty {
        width: 100%;
        height: 175px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 38px;
    }

    .favorito-body {
        padding: 16px;
    }

    .favoritos-scroll {
        max-height: 760px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 6px;
    }

    .favoritos-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .favoritos-scroll::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 20px;
    }

    @media (max-width: 768px) {
        .favoritos-scroll {
            max-height: none;
            overflow: visible;
        }
    }
</style>

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Productos favoritos</h4>
            <p class="text-muted mb-0">
                Favoritos guardados:
                <span class="badge bg-dark">{{ $favoritos->count() }}</span>
            </p>
        </div>

        <a href="{{ route('cliente.catalogo.index') }}" class="btn btn-warning text-white fw-bold">
            <i class="fa-solid fa-store"></i>
            Ir al catálogo
        </a>
    </div>

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

    @if($favoritos->count() > 0)

        <div class="favoritos-scroll">
            <div class="row g-4">

                @foreach($favoritos as $favorito)
                    @php
                        $producto = $favorito->producto;
                    @endphp

                    @if($producto)
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <div class="favorito-card">

                                @if($producto->imagenPrincipal)
                                    <img
                                        src="{{ asset('storage/' . $producto->imagenPrincipal->imagen) }}"
                                        alt="{{ $producto->nombre }}"
                                        class="favorito-img"
                                    >
                                @else
                                    <div class="favorito-img-empty">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                @endif

                                <div class="favorito-body">

                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <div>
                                            <h5 class="fw-bold mb-1">
                                                {{ $producto->nombre }}
                                            </h5>

                                            <small class="text-muted">
                                                {{ $producto->local->nombre ?? 'Local no disponible' }}
                                            </small>
                                        </div>

                                        <span class="badge bg-danger">
                                            <i class="fa-solid fa-heart"></i>
                                        </span>
                                    </div>

                                    <p class="text-muted small mb-2">
                                        {{ $producto->descripcion ?? 'Sin descripción' }}
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

                                    <div class="mt-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">Precio</small>

                                            @if($producto->precio_oferta)
                                                <h5 class="fw-bold mb-0 text-warning">
                                                    ${{ number_format($producto->precio_oferta, 2) }}
                                                </h5>

                                                <small class="text-muted text-decoration-line-through">
                                                    ${{ number_format($producto->precio, 2) }}
                                                </small>
                                            @else
                                                <h5 class="fw-bold mb-0">
                                                    ${{ number_format($producto->precio, 2) }}
                                                </h5>
                                            @endif
                                        </div>

                                        <div class="text-end">
                                            <small class="text-muted">Stock</small>
                                            <h6 class="fw-bold mb-0">
                                                {{ $producto->stock->stock ?? 0 }}
                                            </h6>
                                        </div>
                                    </div>

                                    @if(($producto->stock->stock ?? 0) <= 0)
                                        <div class="mt-3">
                                            <span class="badge bg-danger w-100 py-2">
                                                Sin stock
                                            </span>
                                        </div>
                                    @endif

                                    <div class="mt-3 d-flex gap-2">

                                        <a href="{{ route('cliente.catalogo.producto', $producto->id) }}"
                                           class="btn btn-outline-dark fw-bold w-50">
                                            <i class="fa-solid fa-eye"></i>
                                            Ver
                                        </a>

                                        <form method="POST"
                                              action="{{ route('cliente.carrito.agregar', $producto->id) }}"
                                              class="w-50">
                                            @csrf

                                            <input type="hidden" name="cantidad" value="1">

                                            <button type="submit"
                                                    class="btn btn-warning text-white fw-bold w-100"
                                                    {{ ($producto->stock->stock ?? 0) <= 0 ? 'disabled' : '' }}>
                                                <i class="fa-solid fa-cart-plus"></i>
                                                Agregar
                                            </button>
                                        </form>

                                    </div>

                                    <form method="POST"
                                          action="{{ route('cliente.productos.favorito', $producto->id) }}"
                                          class="mt-2">
                                        @csrf

                                        <button type="submit" class="btn btn-danger fw-bold w-100">
                                            <i class="fa-solid fa-heart-crack"></i>
                                            Quitar de favoritos
                                        </button>
                                    </form>

                                </div>

                            </div>
                        </div>
                    @endif
                @endforeach

            </div>
        </div>

    @else

        <div class="text-center py-5">
            <i class="fa-regular fa-heart fa-3x text-muted mb-3"></i>

            <h5 class="fw-bold">Todavía no tienes favoritos</h5>

            <p class="text-muted">
                Guarda productos como favoritos para encontrarlos rápido después.
            </p>

            <a href="{{ route('cliente.catalogo.index') }}" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-store"></i>
                Explorar catálogo
            </a>
        </div>

    @endif

</div>

@endsection