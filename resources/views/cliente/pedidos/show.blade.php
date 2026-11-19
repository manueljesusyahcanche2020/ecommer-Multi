@extends('layouts.dashboard')

@section('title', 'Detalle pedido')
@section('page-title', 'Detalle del pedido')
@section('page-subtitle', 'Consulta la información completa de tu pedido')

@section('content')

<style>
    .info-box {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 16px;
        background: #f9fafb;
        height: 100%;
    }

    .resumen-box {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 20px;
        padding: 20px;
    }

    .map-container {
        width: 100%;
        height: 280px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
</style>

<div class="page-card">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Pedido {{ $pedido->codigo }}</h4>
            <p class="text-muted mb-0">
                Local: <strong>{{ $pedido->local->nombre ?? 'Local eliminado' }}</strong>
            </p>
        </div>

        <a href="{{ route('cliente.pedidos.ver') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-arrow-left me-1"></i>
            Volver
        </a>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- CARDS DE INFORMACIÓN RÁPIDA --}}
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="info-box">
                <small class="text-muted">Estado del pedido</small>

                <div class="mt-2">
                    @if($pedido->estado_pedido == 'pendiente')
                        <span class="badge bg-warning text-dark">Pendiente</span>
                    @elseif($pedido->estado_pedido == 'aceptado')
                        <span class="badge bg-info text-dark">Aceptado</span>
                    @elseif($pedido->estado_pedido == 'preparando')
                        <span class="badge bg-primary">Preparando</span>
                    @elseif($pedido->estado_pedido == 'en_camino')
                        <span class="badge bg-dark">En camino</span>
                    @elseif($pedido->estado_pedido == 'entregado')
                        <span class="badge bg-success">Entregado</span>
                    @else
                        <span class="badge bg-danger">Cancelado</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="info-box">
                <small class="text-muted">Tipo de entrega</small>
                <h6 class="mb-0 mt-2">
                    {{ $pedido->tipo_entrega == 'delivery' ? 'Entrega a domicilio' : 'Recoger en local' }}
                </h6>
            </div>
        </div>

        <div class="col-md-3">
            <div class="info-box">
                <small class="text-muted">Método de pago</small>
                <h6 class="mb-0 mt-2 text-capitalize">
                    {{ $pedido->metodo_pago }}
                </h6>
            </div>
        </div>

        <div class="col-md-3">
            <div class="info-box">
                <small class="text-muted">Total</small>
                <h5 class="fw-bold mb-0 mt-2">
                    ${{ number_format($pedido->total, 2) }}
                </h5>
            </div>
        </div>

    </div>

    {{-- NOTAS DEL PEDIDO --}}
    @if($pedido->notas_cliente)
        <div class="alert alert-light border mb-3">
            <strong>Tu nota:</strong>
            {{ $pedido->notas_cliente }}
        </div>
    @endif

    @if($pedido->notas_vendedor)
        <div class="alert alert-info mb-3">
            <strong>Nota del vendedor:</strong>
            {{ $pedido->notas_vendedor }}
        </div>
    @endif

    {{-- SECCIÓN DE DIRECCIÓN Y MAPA (DELIVERY / RECOGER) --}}
    @if($pedido->tipo_entrega == 'delivery')
        <div class="info-box mb-4">
            <h5 class="fw-bold mb-3">
                <i class="fa-solid fa-location-dot text-warning me-1"></i>
                Dirección de entrega
            </h5>

            @if($pedido->direccion)
                <div class="row g-3">

                    <div class="col-md-6">
                        <small class="text-muted">Recibe</small>
                        <h6 class="mb-0">
                            {{ $pedido->direccion->nombre_recibe ?? 'Sin nombre' }}
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Teléfono</small>
                        <h6 class="mb-0">
                            {{ $pedido->direccion->telefono ?? 'Sin teléfono' }}
                        </h6>
                    </div>

                    <div class="col-md-8">
                        <small class="text-muted">Calle</small>
                        <h6 class="mb-0">
                            {{ $pedido->direccion->calle ?? 'Sin calle' }}
                            {{ $pedido->direccion->numero ? '#'.$pedido->direccion->numero : '' }}
                        </h6>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">Cruzamientos</small>
                        <h6 class="mb-0">
                            {{ $pedido->direccion->cruzamientos ?? 'Sin cruzamientos' }}
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Colonia</small>
                        <h6 class="mb-0">
                            {{ $pedido->direccion->colonia ?? 'Sin colonia' }}
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Ciudad / Estado</small>
                        <h6 class="mb-0">
                            {{ $pedido->direccion->ciudad ?? 'Sin ciudad' }},
                            {{ $pedido->direccion->estado ?? 'Sin estado' }}
                            {{ $pedido->direccion->codigo_postal ? ' CP '.$pedido->direccion->codigo_postal : '' }}
                        </h6>
                    </div>

                    <div class="col-12">
                        <small class="text-muted">Referencia</small>
                        <h6 class="mb-0">
                            {{ $pedido->direccion->referencia ?? 'Sin referencia' }}
                        </h6>
                    </div>

                    {{-- MAPA GOOGLE MAPS PARA CLIENTE --}}
                    <div class="col-12 mt-3">
                        @if(!empty($pedido->direccion->latitud) && !empty($pedido->direccion->longitud))
                            <div class="map-container mb-2">
                                <iframe
                                    width="100%"
                                    height="100%"
                                    style="border:0;"
                                    loading="lazy"
                                    allowfullscreen
                                    src="https://maps.google.com/maps?q={{ $pedido->direccion->latitud }},{{ $pedido->direccion->longitud }}&z=16&output=embed">
                                </iframe>
                            </div>
                            <a
                                href="https://www.google.com/maps?q={{ $pedido->direccion->latitud }},{{ $pedido->direccion->longitud }}"
                                target="_blank"
                                class="btn btn-sm btn-outline-dark fw-bold">
                                <i class="fa-solid fa-map-location-dot me-1"></i>
                                Ver ubicación en Google Maps
                            </a>
                        @else
                            @php
                                $direccionTexto = implode(', ', array_filter([
                                    $pedido->direccion->calle . ($pedido->direccion->numero ? ' ' . $pedido->direccion->numero : ''),
                                    $pedido->direccion->colonia,
                                    $pedido->direccion->ciudad,
                                    $pedido->direccion->estado
                                ]));
                            @endphp

                            @if(!empty(trim($direccionTexto)))
                                <div class="map-container mb-2">
                                    <iframe
                                        width="100%"
                                        height="100%"
                                        style="border:0;"
                                        loading="lazy"
                                        allowfullscreen
                                        src="https://maps.google.com/maps?q={{ urlencode($direccionTexto) }}&z=15&output=embed">
                                    </iframe>
                                </div>
                                <a
                                    href="https://www.google.com/maps/search/?api=1&query={{ urlencode($direccionTexto) }}"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-dark fw-bold">
                                    <i class="fa-solid fa-map-location-dot me-1"></i>
                                    Buscar dirección en Google Maps
                                </a>
                            @endif
                        @endif
                    </div>

                </div>
            @else
                <p class="text-muted mb-0">
                    Este pedido no tiene dirección asignada.
                </p>
            @endif
        </div>

    @elseif($pedido->tipo_entrega == 'recoger')

        {{-- VISTA SI EL PEDIDO ES PARA RECOGER EN TIENDA --}}
        <div class="info-box mb-4">
            <h5 class="fw-bold mb-3">
                <i class="fa-solid fa-store text-warning me-1"></i>
                Punto de entrega (Dirección de la tienda)
            </h5>

            <div class="alert alert-warning border-0 fw-semibold mb-3">
                <i class="fa-solid fa-bag-shopping me-1"></i>
                Debes acudir a la tienda para recoger tu pedido.
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <small class="text-muted">Nombre del local</small>
                    <h6 class="mb-0 fw-bold">{{ $pedido->local->nombre ?? 'Local no disponible' }}</h6>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Teléfono de la tienda</small>
                    <h6 class="mb-0">{{ $pedido->local->telefono ?? 'Sin teléfono' }}</h6>
                </div>

                <div class="col-12">
                    <small class="text-muted">Dirección de la tienda</small>
                    <h6 class="mb-0">{{ $pedido->local->direccion ?? 'Sin dirección registrada' }}</h6>
                </div>

                {{-- MAPA GOOGLE MAPS PARA TIENDA --}}
                <div class="col-12 mt-3">
                    @if(!empty($pedido->local->latitud) && !empty($pedido->local->longitud))
                        <div class="map-container mb-2">
                            <iframe
                                width="100%"
                                height="100%"
                                style="border:0;"
                                loading="lazy"
                                allowfullscreen
                                src="https://maps.google.com/maps?q={{ $pedido->local->latitud }},{{ $pedido->local->longitud }}&z=16&output=embed">
                            </iframe>
                        </div>
                        <a
                            href="https://www.google.com/maps?q={{ $pedido->local->latitud }},{{ $pedido->local->longitud }}"
                            target="_blank"
                            class="btn btn-sm btn-outline-dark fw-bold">
                            <i class="fa-solid fa-map-location-dot me-1"></i>
                            Ver ubicación de la tienda en Google Maps
                        </a>
                    @elseif(!empty($pedido->local->direccion))
                        <div class="map-container mb-2">
                            <iframe
                                width="100%"
                                height="100%"
                                style="border:0;"
                                loading="lazy"
                                allowfullscreen
                                src="https://maps.google.com/maps?q={{ urlencode($pedido->local->direccion) }}&z=15&output=embed">
                            </iframe>
                        </div>
                        <a
                            href="https://www.google.com/maps/search/?api=1&query={{ urlencode($pedido->local->direccion) }}"
                            target="_blank"
                            class="btn btn-sm btn-outline-dark fw-bold">
                            <i class="fa-solid fa-map-location-dot me-1"></i>
                            Buscar tienda en Google Maps
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- TABLA DE PRODUCTOS --}}
    <h5 class="fw-bold mb-3">Productos del pedido</h5>

    <div class="table-responsive mb-4">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Variante</th>
                    <th>Cantidad</th>
                    <th>Precio unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @foreach($pedido->detalles as $detalle)
                    <tr>
                        <td>
                            <strong>{{ $detalle->nombre_producto }}</strong>
                        </td>

                        <td>
                            {{ $detalle->nombre_variante ?? 'Sin variante' }}
                        </td>

                        <td>
                            <span class="badge bg-secondary">
                                {{ $detalle->cantidad }}
                            </span>
                        </td>

                        <td>
                            ${{ number_format($detalle->precio_unitario, 2) }}
                        </td>

                        <td>
                            <strong>${{ number_format($detalle->subtotal, 2) }}</strong>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- DATOS INFERIORES --}}
    <div class="row g-3">

        <div class="col-md-6">
            <div class="info-box">
                <h5 class="fw-bold mb-3">Información del local</h5>

                <p class="mb-1">
                    <strong>{{ $pedido->local->nombre ?? 'Local eliminado' }}</strong>
                </p>

                <p class="text-muted mb-1">
                    {{ $pedido->local->direccion ?? 'Sin dirección' }}
                </p>

                <p class="text-muted mb-0">
                    {{ $pedido->local->telefono ?? 'Sin teléfono' }}
                </p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="resumen-box">
                <h5 class="fw-bold mb-3">Resumen de pago</h5>

                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <strong>${{ number_format($pedido->subtotal, 2) }}</strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Envío</span>
                    <strong>${{ number_format($pedido->costo_envio, 2) }}</strong>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Descuento</span>
                    <strong>${{ number_format($pedido->descuento, 2) }}</strong>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Total</span>
                    <h4 class="fw-bold mb-0">
                        ${{ number_format($pedido->total, 2) }}
                    </h4>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection