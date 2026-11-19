@extends('layouts.dashboard')

@section('title', $local->nombre)
@section('page-title', $local->nombre)
@section('page-subtitle', 'Productos disponibles del local')

@section('content')

@php
    $telefonoLimpio = preg_replace('/\D+/', '', (string) $local->telefono);

    // WhatsApp: si tiene 10 dígitos se asume México (52)
    $waNumero = preg_replace('/\D+/', '', (string) $local->whatsapp);
    if (strlen($waNumero) === 10) {
        $waNumero = '52' . $waNumero;
    }

    // Google Maps: con coordenadas si existen, si no con la dirección escrita
    if ($local->latitud && $local->longitud) {
        $consultaMapa = $local->latitud . ',' . $local->longitud;
    } else {
        $consultaMapa = implode(', ', array_filter([$local->direccion, $local->ciudad, $local->estado]));
    }

    $mapsUrl = $consultaMapa
        ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($consultaMapa)
        : null;
@endphp

<style>
    /* Panel del local: se queda fijo mientras se hace scroll en los productos */
    .local-sticky { position:sticky; top:90px; align-self:flex-start; }

    .local-hero { border-radius:24px; overflow:hidden; border:1px solid #e5e7eb; background:white; box-shadow:0 10px 25px rgba(15,23,42,.06); }
    .local-portada { width:100%; height:160px; object-fit:cover; background:#f3f4f6; }
    .local-portada-empty { width:100%; height:160px; background:#f3f4f6; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:40px; }
    .local-info-box { padding:0 20px 20px; }
    .local-logo { width:84px; height:84px; object-fit:cover; border-radius:22px; border:5px solid white; margin-top:-42px; background:white; box-shadow:0 10px 25px rgba(15,23,42,.15); }
    .local-logo-empty { width:84px; height:84px; border-radius:22px; border:5px solid white; margin-top:-42px; background:#fff7ed; display:flex; align-items:center; justify-content:center; color:#f97316; font-size:30px; box-shadow:0 10px 25px rgba(15,23,42,.15); }

    .producto-card { border:1px solid #e5e7eb; border-radius:22px; background:white; overflow:hidden; height:100%; box-shadow:0 10px 25px rgba(15,23,42,.06); transition:all .2s ease; }
    .producto-card:hover { transform:translateY(-3px); box-shadow:0 16px 35px rgba(15,23,42,.10); }
    .producto-img { width:100%; height:170px; object-fit:cover; background:#f3f4f6; }
    .producto-img-empty { width:100%; height:170px; background:#f3f4f6; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:36px; }
    .producto-body { padding:16px; }

    .envio-item { border:1px solid #e5e7eb; border-radius:16px; padding:12px 14px; }
    .envio-item.fuera { background:#f9fafb; }

    @media (max-width:991px) {
        .local-sticky { position:static; }
    }
</style>

<div class="page-card">

    <div class="mb-3">
        <a href="{{ route('cliente.catalogo.index') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-arrow-left"></i> Volver al catálogo
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

    {{-- MENSAJE DE ERROR --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- ============ COLUMNA IZQUIERDA: INFORMACIÓN DEL LOCAL (FIJA) ============ --}}
        <div class="col-lg-4 col-xl-3 d-flex">
            <div class="local-sticky w-100">
                <div class="local-hero">

                    @if($local->portada)
                        <img src="{{ asset('storage/' . $local->portada) }}" alt="{{ $local->nombre }}" class="local-portada">
                    @else
                        <div class="local-portada-empty"><i class="fa-solid fa-store"></i></div>
                    @endif

                    <div class="local-info-box">

                        @if($local->logo)
                            <img src="{{ asset('storage/' . $local->logo) }}" alt="{{ $local->nombre }}" class="local-logo">
                        @else
                            <div class="local-logo-empty"><i class="fa-solid fa-shop"></i></div>
                        @endif

                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                <h4 class="fw-bold mb-1">{{ $local->nombre }}</h4>

                                @if($local->estado_local == 'abierto')
                                    <span class="badge bg-success">Abierto</span>
                                @elseif($local->estado_local == 'pausado')
                                    <span class="badge bg-warning text-dark">Pausado</span>
                                @else
                                    <span class="badge bg-secondary">Cerrado</span>
                                @endif
                            </div>

                            <p class="text-muted mb-2">{{ $local->descripcion ?? 'Sin descripción' }}</p>

                            <p class="text-muted small mb-1">
                                <i class="fa-solid fa-location-dot text-warning"></i>
                                {{ $local->direccion ?? 'Sin dirección' }}
                            </p>

                            <p class="text-muted small mb-2">
                                {{ $local->ciudad ?? 'Sin ciudad' }}{{ $local->estado ? ', '.$local->estado : '' }}
                            </p>

                            <div class="mb-1">
                                @if($local->permite_delivery)
                                    <span class="badge bg-info text-dark">Delivery</span>
                                @endif

                                @if($local->permite_recoger)
                                    <span class="badge bg-light text-dark">Recoger</span>
                                @endif
                            </div>
                        </div>

                        <hr>

                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted">Teléfono</small>
                                <h6 class="mb-0 small fw-bold">{{ $local->telefono ?? 'Sin teléfono' }}</h6>
                            </div>

                            <div class="col-6">
                                <small class="text-muted">WhatsApp</small>
                                <h6 class="mb-0 small fw-bold">{{ $local->whatsapp ?? 'Sin WhatsApp' }}</h6>
                            </div>

                            <div class="col-6">
                                <small class="text-muted">Pedido mínimo</small>
                                <h6 class="mb-0 small fw-bold">${{ number_format($local->minimo_pedido ?? 0, 2) }}</h6>
                            </div>

                            {{-- COSTO DE ENVÍO hacia tu dirección principal --}}
                            <div class="col-6">
                                <small class="text-muted">Envío a {{ $ciudadCliente }}</small>
                                <h6 class="mb-0 small fw-bold">
                                    @if(!$local->permite_delivery)
                                        Sin delivery
                                    @elseif($costoEnvio === null)
                                        No disponible
                                    @elseif($costoEnvio == 0)
                                        Gratis
                                    @else
                                        ${{ number_format($costoEnvio, 2) }}
                                    @endif
                                </h6>
                            </div>

                            {{-- ACCIONES: llamar, WhatsApp, Google Maps --}}
                            <div class="col-12">
                                <button type="button"
                                        class="btn btn-sm btn-warning text-white fw-bold w-100"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalAcciones">
                                    <i class="fa-solid fa-ellipsis"></i>
                                    Acciones
                                </button>
                            </div>

                            @if($local->permite_delivery)
                                <div class="col-12">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-warning fw-bold w-100"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEnvio">
                                        <i class="fa-solid fa-truck-fast"></i>
                                        Ver envío a mis direcciones
                                    </button>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ============ COLUMNA DERECHA: PRODUCTOS (CON SCROLL) ============ --}}
        <div class="col-lg-8 col-xl-9">

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1">Productos de {{ $local->nombre }}</h4>
                    <p class="text-muted mb-0">
                        Productos disponibles:
                        <span class="badge bg-dark">{{ $productos->count() }}</span>
                    </p>
                </div>

                <a href="{{ route('cliente.carrito.index') }}" class="btn btn-outline-dark fw-bold">
                    <i class="fa-solid fa-cart-shopping"></i> Ver carrito
                </a>
            </div>

            @if($productos->count() > 0)

                <div class="row g-4">

                    @foreach($productos as $producto)
                        @php
                            // Sin variantes: su única variante ("Estándar") es lo que se compra
                            $unica = $producto->es_variable ? null : $producto->variantes->first();
                        @endphp

                        <div class="col-xl-4 col-md-6">
                            <div class="producto-card">

                                @if($producto->imagenPrincipal)
                                    <img src="{{ asset('storage/' . $producto->imagenPrincipal->imagen) }}"
                                         alt="{{ $producto->nombre }}" class="producto-img">
                                @else
                                    <div class="producto-img-empty"><i class="fa-solid fa-image"></i></div>
                                @endif

                                <div class="producto-body">

                                    <h5 class="fw-bold mb-1">{{ $producto->nombre }}</h5>

                                    <p class="text-muted small mb-2">
                                        {{ $producto->descripcion ?? 'Sin descripción' }}
                                    </p>

                                    <div class="mb-2">
                                        @if($producto->categoria)
                                            <span class="badge bg-info text-dark">{{ $producto->categoria->nombre }}</span>
                                        @else
                                            <span class="badge bg-light text-dark">Sin categoría</span>
                                        @endif

                                        @if($producto->destacado)
                                            <span class="badge bg-warning text-dark">Destacado</span>
                                        @endif
                                    </div>

                                    {{-- PRECIO Y STOCK (salen de las variantes) --}}
                                    <div class="mt-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">{{ $producto->es_variable ? 'Desde' : 'Precio' }}</small>

                                            <h5 class="fw-bold mb-0 {{ $producto->precio_antes ? 'text-warning' : '' }}">
                                                ${{ number_format($producto->precio_desde, 2) }}
                                            </h5>

                                            @if($producto->precio_antes)
                                                <small class="text-muted text-decoration-line-through">
                                                    ${{ number_format($producto->precio_antes, 2) }}
                                                </small>
                                            @endif
                                        </div>

                                        <div class="text-end">
                                            @if($producto->es_variable)
                                                <small class="text-muted">Opciones</small>
                                                <h6 class="fw-bold mb-0">{{ $producto->variantes->count() }}</h6>
                                            @else
                                                <small class="text-muted">Stock</small>
                                                <h6 class="fw-bold mb-0">
                                                    {{ ($unica && $unica->stock_disponible !== null) ? $unica->stock_disponible : 'Disponible' }}
                                                </h6>
                                            @endif
                                        </div>
                                    </div>

                                    @unless($producto->disponible)
                                        <div class="mt-3">
                                            <span class="badge bg-danger w-100 py-2">Sin stock</span>
                                        </div>
                                    @endunless

                                    {{-- BOTONES VER Y AGREGAR --}}
                                    <div class="mt-3 d-flex gap-2">

                                        <a href="{{ route('cliente.catalogo.producto', $producto->id) }}"
                                           class="btn btn-outline-dark fw-bold w-50">
                                            <i class="fa-solid fa-eye"></i> Ver
                                        </a>

                                        @if($producto->es_variable)
                                            {{-- Con variantes hay que elegir una: se hace en el detalle --}}
                                            <a href="{{ route('cliente.catalogo.producto', $producto->id) }}"
                                               class="btn btn-warning text-white fw-bold w-50 {{ $producto->disponible ? '' : 'disabled' }}">
                                                <i class="fa-solid fa-list"></i> Elegir
                                            </a>
                                        @else
                                            <form method="POST"
                                                  action="{{ route('cliente.carrito.agregar', $producto->id) }}"
                                                  class="w-50">
                                                @csrf

                                                <input type="hidden" name="variante_id" value="{{ $unica->id ?? '' }}">
                                                <input type="hidden" name="cantidad" value="1">

                                                <button type="submit"
                                                        class="btn btn-warning text-white fw-bold w-100"
                                                        {{ $producto->disponible ? '' : 'disabled' }}>
                                                    <i class="fa-solid fa-cart-plus"></i> Agregar
                                                </button>
                                            </form>
                                        @endif

                                    </div>

                                    {{-- BOTÓN FAVORITO --}}
                                    <form method="POST"
                                          action="{{ route('cliente.productos.favorito', $producto->id) }}"
                                          class="mt-2">
                                        @csrf

                                        <button type="submit"
                                                class="btn {{ in_array($producto->id, $favoritosIds ?? []) ? 'btn-danger' : 'btn-outline-danger' }} fw-bold w-100">
                                            @if(in_array($producto->id, $favoritosIds ?? []))
                                                <i class="fa-solid fa-heart"></i> Quitar favorito
                                            @else
                                                <i class="fa-regular fa-heart"></i> Favorito
                                            @endif
                                        </button>
                                    </form>

                                </div>

                            </div>
                        </div>
                    @endforeach

                </div>

            @else

                <div class="text-center py-5">
                    <i class="fa-solid fa-box-open fa-3x text-muted mb-3"></i>
                    <h5 class="fw-bold">Este local todavía no tiene productos públicos</h5>
                    <p class="text-muted">Vuelve más tarde para ver nuevos productos.</p>
                </div>

            @endif

        </div>
    </div>

</div>

{{-- ============ MODAL: ACCIONES DEL LOCAL ============ --}}
<div class="modal fade" id="modalAcciones" tabindex="-1" aria-labelledby="modalAccionesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:22px;">

            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalAccionesLabel">
                    <i class="fa-solid fa-store text-warning"></i>
                    {{ $local->nombre }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="d-grid gap-2">

                    {{-- Llamar --}}
                    @if($telefonoLimpio)
                        <a href="tel:{{ $telefonoLimpio }}" class="btn btn-outline-dark fw-bold text-start">
                            <i class="fa-solid fa-phone me-2"></i>
                            Llamar al local
                            <small class="text-muted d-block fw-normal">{{ $local->telefono }}</small>
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-secondary text-start" disabled>
                            <i class="fa-solid fa-phone-slash me-2"></i> Sin teléfono registrado
                        </button>
                    @endif

                    {{-- WhatsApp --}}
                    @if($waNumero)
                        <a href="https://wa.me/{{ $waNumero }}" target="_blank" rel="noopener"
                           class="btn btn-outline-success fw-bold text-start">
                            <i class="fa-brands fa-whatsapp me-2"></i>
                            Escribir por WhatsApp
                            <small class="text-muted d-block fw-normal">{{ $local->whatsapp }}</small>
                        </a>
                    @endif

                    {{-- Google Maps --}}
                    @if($mapsUrl)
                        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener"
                           class="btn btn-outline-primary fw-bold text-start">
                            <i class="fa-solid fa-map-location-dot me-2"></i>
                            Ver en Google Maps
                            <small class="text-muted d-block fw-normal">
                                {{ $local->direccion ?? $local->ciudad }}
                            </small>
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-secondary text-start" disabled>
                            <i class="fa-solid fa-location-crosshairs me-2"></i> Sin ubicación registrada
                        </button>
                    @endif

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-warning text-white fw-bold" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ============ MODAL: COSTO DE ENVÍO A MIS DIRECCIONES ============ --}}
@if($local->permite_delivery)
    <div class="modal fade" id="modalEnvio" tabindex="-1" aria-labelledby="modalEnvioLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:22px;">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalEnvioLabel">
                        <i class="fa-solid fa-truck-fast text-warning"></i>
                        Costo de envío · {{ $local->nombre }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">

                    {{-- MIS DIRECCIONES --}}
                    <h6 class="fw-bold mb-2">Hacia tus direcciones</h6>

                    @forelse($envios as $envio)
                        <div class="envio-item mb-2 {{ $envio['cubre'] ? '' : 'fuera' }}">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <strong>{{ $envio['ciudad'] }}</strong>

                                    @if($envio['principal'])
                                        <span class="badge bg-dark ms-1">Principal</span>
                                    @endif

                                    @if($envio['detalle'])
                                        <br>
                                        <small class="text-muted">{{ $envio['detalle'] }}</small>
                                    @endif
                                </div>

                                <div class="text-end">
                                    @if(!$envio['cubre'])
                                        <span class="badge bg-secondary">Fuera de zona</span>
                                    @elseif($envio['costo'] == 0)
                                        <span class="badge bg-success">Gratis</span>
                                    @else
                                        <strong>${{ number_format($envio['costo'], 2) }}</strong>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-2">
                            Aún no tienes direcciones guardadas.
                            <a href="{{ route('cliente.direcciones.create') }}">Agregar una dirección</a>
                        </p>
                    @endforelse

                    <hr>

                    {{-- ZONAS DONDE ENTREGA EL LOCAL --}}
                    <h6 class="fw-bold mb-2">Zonas de entrega del local</h6>

                    @forelse($zonas as $zona)
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span>
                                <i class="fa-solid fa-location-dot text-warning"></i>
                                {{ $zona['ciudad'] }}
                            </span>

                            @if($zona['costo'] == 0)
                                <span class="badge bg-success">Gratis</span>
                            @else
                                <strong>${{ number_format($zona['costo'], 2) }}</strong>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Este local no tiene zonas de entrega registradas.</p>
                    @endforelse

                </div>

                <div class="modal-footer">
                    <a href="{{ route('cliente.direcciones.index') }}" class="btn btn-outline-secondary fw-bold">
                        Mis direcciones
                    </a>
                    <button type="button" class="btn btn-warning text-white fw-bold" data-bs-dismiss="modal">
                        Entendido
                    </button>
                </div>

            </div>
        </div>
    </div>
@endif

@endsection