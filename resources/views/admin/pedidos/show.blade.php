@extends('layouts.dashboard')

@section('title', 'Detalle del pedido')
@section('page-title', 'Detalle del pedido')
@section('page-subtitle', 'Vista general del pedido desde administración')

@section('content')

<style>
    .info-box {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 18px;
        background: #fff;
    }

    .status-card {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 16px;
        background: #f9fafb;
        height: 100%;
    }

    .status-card small {
        color: #6b7280;
        display: block;
        margin-bottom: 8px;
    }

    .section-title {
        font-weight: 800;
        margin-bottom: 14px;
    }

    .summary-card {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 18px;
        padding: 18px;
    }
</style>

<div class="page-card">

    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Pedido {{ $pedido->codigo }}</h4>
            <p class="text-muted mb-0">
                Vista de administración general
            </p>
        </div>

        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-arrow-left"></i>
            Volver
        </a>
    </div>

    {{-- RESUMEN PRINCIPAL --}}
    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="status-card">
                <small>Estado del pedido</small>

                @if($pedido->estado_pedido === 'pendiente')
                    <span class="badge bg-warning text-dark">Pendiente</span>
                @elseif($pedido->estado_pedido === 'aceptado')
                    <span class="badge bg-info text-dark">Aceptado</span>
                @elseif($pedido->estado_pedido === 'preparando')
                    <span class="badge bg-primary">Preparando</span>
                @elseif($pedido->estado_pedido === 'en_camino')
                    <span class="badge bg-dark">En camino</span>
                @elseif($pedido->estado_pedido === 'entregado')
                    <span class="badge bg-success">Entregado</span>
                @else
                    <span class="badge bg-danger">Cancelado</span>
                @endif
            </div>
        </div>

        <div class="col-md-3">
            <div class="status-card">
                <small>Tipo de entrega</small>
                <strong>
                    {{ $pedido->tipo_entrega === 'delivery' ? 'Entrega a domicilio' : 'Recoger en local' }}
                </strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="status-card">
                <small>Método de pago</small>
                <strong>{{ ucfirst($pedido->metodo_pago) }}</strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="status-card">
                <small>Total</small>
                <h5 class="fw-bold mb-0">
                    ${{ number_format($pedido->total, 2) }}
                </h5>
            </div>
        </div>

    </div>

    {{-- CLIENTE Y LOCAL --}}
    <div class="row g-3 mb-4">

        <div class="col-md-6">
            <div class="info-box h-100">
                <h5 class="section-title">
                    <i class="fa-solid fa-user text-warning"></i>
                    Información del cliente
                </h5>

                <p class="mb-1">
                    <strong>{{ $pedido->cliente->name ?? 'Cliente no disponible' }}</strong>
                </p>

                <p class="mb-1">
                    Correo: {{ $pedido->cliente->email ?? 'Sin correo' }}
                </p>

                <p class="mb-0">
                    ID cliente: {{ $pedido->cliente_id }}
                </p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-box h-100">
                <h5 class="section-title">
                    <i class="fa-solid fa-store text-warning"></i>
                    Información del local
                </h5>

                <p class="mb-1">
                    <strong>{{ $pedido->local->nombre ?? 'Local no disponible' }}</strong>
                </p>

                <p class="mb-1">
                    Teléfono: {{ $pedido->local->telefono ?? 'Sin teléfono' }}
                </p>

                <p class="mb-0">
                    Dirección: {{ $pedido->local->direccion ?? 'Sin dirección' }}
                </p>
            </div>
        </div>

    </div>

    {{-- NOTAS --}}
    <div class="row g-3 mb-4">

        <div class="col-md-6">
            <div class="info-box h-100">
                <h5 class="section-title">
                    <i class="fa-solid fa-message text-warning"></i>
                    Nota del cliente
                </h5>

                <p class="mb-0">
                    {{ $pedido->notas_cliente ?? 'Sin nota del cliente.' }}
                </p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-box h-100">
                <h5 class="section-title">
                    <i class="fa-solid fa-reply text-warning"></i>
                    Nota del vendedor
                </h5>

                <p class="mb-0">
                    {{ $pedido->notas_vendedor ?? 'Sin nota del vendedor.' }}
                </p>
            </div>
        </div>

    </div>

    {{-- DIRECCIÓN --}}
    @if($pedido->tipo_entrega === 'delivery')
        <div class="info-box mb-4">
            <h5 class="section-title">
                <i class="fa-solid fa-location-dot text-warning"></i>
                Dirección de entrega
            </h5>

            @if($pedido->direccion)
                <div class="row g-3">

                    <div class="col-md-6">
                        <small class="text-muted">Recibe</small>
                        <h6>{{ $pedido->direccion->nombre_recibe ?? 'Sin nombre' }}</h6>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Teléfono</small>
                        <h6>{{ $pedido->direccion->telefono ?? 'Sin teléfono' }}</h6>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Calle</small>
                        <h6>
                            {{ $pedido->direccion->calle ?? 'Sin calle' }}
                            {{ $pedido->direccion->numero ? '#'.$pedido->direccion->numero : '' }}
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Cruzamientos</small>
                        <h6>{{ $pedido->direccion->cruzamientos ?? 'Sin cruzamientos' }}</h6>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Colonia</small>
                        <h6>{{ $pedido->direccion->colonia ?? 'Sin colonia' }}</h6>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Ciudad / Estado</small>
                        <h6>
                            {{ $pedido->direccion->ciudad }},
                            {{ $pedido->direccion->estado }}
                            {{ $pedido->direccion->codigo_postal ? 'CP '.$pedido->direccion->codigo_postal : '' }}
                        </h6>
                    </div>

                    <div class="col-12">
                        <small class="text-muted">Referencia</small>
                        <h6>{{ $pedido->direccion->referencia ?? 'Sin referencia' }}</h6>
                    </div>

                    @if($pedido->direccion->latitud && $pedido->direccion->longitud)
                        <div class="col-12">
                            <a href="https://www.google.com/maps?q={{ $pedido->direccion->latitud }},{{ $pedido->direccion->longitud }}"
                               target="_blank"
                               class="btn btn-sm btn-outline-dark fw-bold">
                                <i class="fa-solid fa-map-location-dot"></i>
                                Ver ubicación en Google Maps
                            </a>
                        </div>
                    @endif

                </div>
            @else
                <div class="alert alert-warning mb-0">
                    Este pedido es a domicilio, pero no tiene dirección guardada.
                </div>
            @endif
        </div>
    @else
        <div class="info-box mb-4">
            <h5 class="section-title">
                <i class="fa-solid fa-store text-warning"></i>
                Entrega
            </h5>

            <p class="mb-0">
                El cliente eligió <strong>recoger en local</strong>.
            </p>
        </div>
    @endif

    {{-- PRODUCTOS --}}
    <div class="info-box mb-4">
        <h5 class="section-title">
            <i class="fa-solid fa-box text-warning"></i>
            Productos del pedido
        </h5>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
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
    </div>

    {{-- RESUMEN DE PAGO --}}
    <div class="summary-card">
        <h5 class="section-title">
            <i class="fa-solid fa-money-bill text-warning"></i>
            Resumen de pago
        </h5>

        <div class="d-flex justify-content-between mb-2">
            <span>Subtotal</span>
            <strong>${{ number_format($pedido->subtotal, 2) }}</strong>
        </div>

        <div class="d-flex justify-content-between mb-2">
            <span>Costo de envío</span>
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

@endsection