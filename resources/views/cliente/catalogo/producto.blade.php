@extends('layouts.dashboard')

@section('title', $producto->nombre)
@section('page-title', 'Detalle del producto')
@section('page-subtitle', 'Información completa del producto')

@section('content')

<style>
    .producto-img-main { width:100%; height:420px; object-fit:cover; border-radius:24px; border:1px solid #e5e7eb; background:#f3f4f6; }
    .producto-img-empty { width:100%; height:420px; border-radius:24px; border:1px solid #e5e7eb; background:#f3f4f6; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:50px; }
    .thumb-img { width:82px; height:82px; object-fit:cover; border-radius:14px; border:1px solid #e5e7eb; }
    .info-box { border:1px solid #e5e7eb; border-radius:22px; padding:20px; background:white; box-shadow:0 10px 25px rgba(15,23,42,.05); }
    .variante-card { transition:all .2s ease; background:white; }
    .variante-card:hover { border-color:#f59e0b !important; background:#fff7ed; }
    .variante-card input[type="radio"]:checked { background-color:#f59e0b; border-color:#f59e0b; }
</style>

@php
    // Tope de cantidad cuando la variante no controla stock
    $maxSinLimite = 99;

    // Sin variantes: su única variante ("Estándar") es lo que se compra
    $unica = $producto->es_variable ? null : $producto->variantes->first();

    $maxSimple = ($unica && $unica->stock_disponible !== null) ? max($unica->stock_disponible, 1) : $maxSinLimite;
@endphp

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $producto->nombre }}</h4>
            <p class="text-muted mb-0">
                Local: <strong>{{ $producto->local->nombre ?? 'Local no disponible' }}</strong>
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            @if($producto->local)
                <a href="{{ route('cliente.catalogo.local', $producto->local->id) }}"
                   class="btn btn-outline-secondary fw-bold">
                    <i class="fa-solid fa-arrow-left"></i> Volver al local
                </a>
            @endif

            <a href="{{ route('cliente.carrito.index') }}" class="btn btn-outline-dark fw-bold">
                <i class="fa-solid fa-cart-shopping"></i> Ver carrito
            </a>
        </div>
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

    <div class="row g-4">

        {{-- IMÁGENES --}}
        <div class="col-lg-6">

            @if($producto->imagenPrincipal)
                <img id="imagenPrincipalProducto"
                     src="{{ asset('storage/' . $producto->imagenPrincipal->imagen) }}"
                     alt="{{ $producto->nombre }}" class="producto-img-main">
            @else
                <div id="imagenPrincipalProductoEmpty" class="producto-img-empty">
                    <i class="fa-solid fa-image"></i>
                </div>
            @endif

            @if($producto->imagenes && $producto->imagenes->count() > 0)
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @foreach($producto->imagenes as $imagen)
                        <img src="{{ asset('storage/' . $imagen->imagen) }}"
                             alt="{{ $producto->nombre }}" class="thumb-img" style="cursor:pointer;"
                             onclick="cambiarImagenPrincipal('{{ asset('storage/' . $imagen->imagen) }}')">
                    @endforeach
                </div>
            @endif

        </div>

        {{-- INFO --}}
        <div class="col-lg-6">
            <div class="info-box">

                <div class="mb-3">
                    @if($producto->categoria)
                        <span class="badge bg-info text-dark">{{ $producto->categoria->nombre }}</span>
                    @else
                        <span class="badge bg-light text-dark">Sin categoría</span>
                    @endif

                    @if($producto->destacado)
                        <span class="badge bg-warning text-dark">Destacado</span>
                    @endif

                    @if($producto->es_variable)
                        <span class="badge bg-dark">Producto variable</span>
                    @else
                        <span class="badge bg-secondary">Producto simple</span>
                    @endif
                </div>

                <h2 class="fw-bold mb-2">{{ $producto->nombre }}</h2>

                <p class="text-muted">{{ $producto->descripcion ?? 'Sin descripción disponible.' }}</p>

                {{-- PRECIO --}}
                <div class="mb-4">
                    <small class="text-muted" id="precioEtiqueta">{{ $producto->es_variable ? 'Desde' : 'Precio' }}</small>

                    <h3 class="fw-bold mb-0 {{ $producto->precio_antes ? 'text-warning' : '' }}" id="precioMostrado">
                        ${{ number_format($producto->precio_desde, 2) }}
                    </h3>

                    @if($producto->precio_antes)
                        <small class="text-muted text-decoration-line-through" id="precioAntes">
                            ${{ number_format($producto->precio_antes, 2) }}
                        </small>
                    @endif
                </div>

                {{-- STOCK (solo sin variantes; con variantes se ve en cada una) --}}
                @if(!$producto->es_variable && $unica)
                    <div class="alert {{ $unica->disponible ? 'alert-success' : 'alert-danger' }}">
                        <strong>Stock disponible:</strong>
                        @if(!$unica->disponible)
                            Sin stock
                        @elseif($unica->stock_disponible === null)
                            Disponible
                        @else
                            {{ $unica->stock_disponible }}
                        @endif
                    </div>
                @endif

                {{-- FORM AGREGAR CARRITO --}}
                <form method="POST" action="{{ route('cliente.carrito.agregar', $producto->id) }}">
                    @csrf

                    {{-- Sin variantes: se manda su única variante --}}
                    @if(!$producto->es_variable && $unica)
                        <input type="hidden" name="variante_id" value="{{ $unica->id }}">
                    @endif

                    {{-- VARIANTES --}}
                    @if($producto->es_variable)

                        <div class="mb-4">
                            <h5 class="fw-bold mb-2">Selecciona una variante</h5>

                            <div class="row g-3">

                                @foreach($producto->variantes as $variante)
                                    <div class="col-md-6">
                                        <label class="border rounded-4 p-3 w-100 h-100 variante-card"
                                               style="cursor:pointer; display:block;">

                                            <div class="d-flex gap-3 align-items-center">

                                                @if($variante->imagen)
                                                    <img src="{{ asset('storage/' . $variante->imagen) }}"
                                                         alt="{{ $variante->nombre }} {{ $variante->valor }}"
                                                         style="width:70px; height:70px; object-fit:cover; border-radius:14px; border:1px solid #e5e7eb;"
                                                         onclick="cambiarImagenPrincipal('{{ asset('storage/' . $variante->imagen) }}')">
                                                @else
                                                    <div style="width:70px; height:70px; border-radius:14px; background:#f3f4f6; display:flex; align-items:center; justify-content:center; color:#9ca3af;">
                                                        <i class="fa-solid fa-image"></i>
                                                    </div>
                                                @endif

                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between gap-2">
                                                        <strong>
                                                            {{ $variante->nombre }}{{ $variante->valor ? ': ' . $variante->valor : '' }}
                                                        </strong>

                                                        <input type="radio"
                                                               name="variante_id"
                                                               value="{{ $variante->id }}"
                                                               class="form-check-input variante-radio"
                                                               data-stock="{{ $variante->stock_disponible ?? '' }}"
                                                               data-precio="{{ number_format($variante->precio_final, 2) }}"
                                                               data-precio-antes="{{ $variante->tiene_oferta ? number_format($variante->precio_venta, 2) : '' }}"
                                                               data-imagen="{{ $variante->imagen ? asset('storage/' . $variante->imagen) : '' }}"
                                                               {{ $variante->disponible ? '' : 'disabled' }}
                                                               required>
                                                    </div>

                                                    <small class="text-muted d-block">
                                                        SKU: {{ $variante->sku ?? 'Sin SKU' }}
                                                    </small>

                                                    <small class="text-muted d-block">
                                                        Stock:
                                                        {{ $variante->stock_disponible === null ? 'Disponible' : $variante->stock_disponible }}
                                                    </small>

                                                    <strong class="d-block mt-1 {{ $variante->tiene_oferta ? 'text-warning' : '' }}">
                                                        ${{ number_format($variante->precio_final, 2) }}
                                                    </strong>

                                                    @if($variante->tiene_oferta)
                                                        <small class="text-muted text-decoration-line-through">
                                                            ${{ number_format($variante->precio_venta, 2) }}
                                                        </small>
                                                    @endif

                                                    @unless($variante->disponible)
                                                        <span class="badge bg-danger mt-1">Sin stock</span>
                                                    @endunless
                                                </div>

                                            </div>
                                        </label>
                                    </div>
                                @endforeach

                            </div>
                        </div>

                    @endif

                    {{-- CANTIDAD --}}
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Cantidad</label>
                            <input type="number"
                                   name="cantidad"
                                   id="cantidadInput"
                                   class="form-control"
                                   value="1"
                                   min="1"
                                   max="{{ $producto->es_variable ? 1 : $maxSimple }}">
                        </div>

                        <div class="col-md-8">
                            <button type="submit"
                                    id="btnAgregarCarrito"
                                    class="btn btn-warning text-white fw-bold w-100"
                                    {{ (!$producto->es_variable && (!$unica || !$unica->disponible)) ? 'disabled' : '' }}>
                                <i class="fa-solid fa-cart-plus"></i>
                                Agregar al carrito
                            </button>
                        </div>
                    </div>
                </form>

                @if($producto->es_variable)
                    <div class="alert alert-light border mt-3 mb-0">
                        <i class="fa-solid fa-circle-info"></i>
                        Selecciona una variante para agregar este producto al carrito.
                    </div>
                @endif

            </div>
        </div>

    </div>

</div>

<script>
    const MAX_SIN_LIMITE = {{ $maxSinLimite }};

    function cambiarImagenPrincipal(url) {
        const img = document.getElementById('imagenPrincipalProducto');
        const empty = document.getElementById('imagenPrincipalProductoEmpty');

        if (img) {
            img.src = url;
        }

        if (empty) {
            empty.outerHTML = `
                <img id="imagenPrincipalProducto" src="${url}" class="producto-img-main">
            `;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const radios = document.querySelectorAll('.variante-radio');
        const cantidadInput = document.getElementById('cantidadInput');
        const btnAgregar = document.getElementById('btnAgregarCarrito');

        if (radios.length === 0) {
            return;
        }

        btnAgregar.disabled = true;

        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                // data-stock vacío = la variante no controla stock
                const stock = this.dataset.stock === '' ? MAX_SIN_LIMITE : Number(this.dataset.stock);
                const imagen = this.dataset.imagen;

                cantidadInput.max = stock;
                cantidadInput.value = 1;
                btnAgregar.disabled = stock <= 0;

                // Precio de la variante elegida
                document.getElementById('precioEtiqueta').textContent = 'Precio';
                document.getElementById('precioMostrado').textContent = '$' + this.dataset.precio;

                let antes = document.getElementById('precioAntes');

                if (this.dataset.precioAntes) {
                    if (!antes) {
                        antes = document.createElement('small');
                        antes.id = 'precioAntes';
                        antes.className = 'text-muted text-decoration-line-through';
                        document.getElementById('precioMostrado').after(antes);
                    }
                    antes.textContent = '$' + this.dataset.precioAntes;
                } else if (antes) {
                    antes.remove();
                }

                if (imagen) {
                    cambiarImagenPrincipal(imagen);
                }
            });
        });
    });
</script>

@endsection