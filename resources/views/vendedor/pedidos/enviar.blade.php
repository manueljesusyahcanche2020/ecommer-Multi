@extends('layouts.dashboard')

@section('title', 'Pedidos por enviar')
@section('page-title', 'Pedidos por enviar')
@section('page-subtitle', 'Pedidos listos para mandar o entregar')

@section('content')

<style>
    .pedido-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,.04);
    }

    .pedido-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .pedido-main {
        flex: 1;
        min-width: 260px;
    }

    .pedido-code {
        font-weight: 800;
        font-size: 1.1rem;
        margin-bottom: 4px;
    }

    .pedido-info {
        color: #6b7280;
        margin-bottom: 4px;
        font-size: .92rem;
    }

    .pedido-total {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 12px;
        padding: 10px 12px;
        font-weight: 800;
        text-align: right;
        min-width: 100px;
    }

    .pedido-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    .pedido-actions form {
        margin: 0;
    }

    .empty-box {
        background: #f9fafb;
        border: 1px dashed #d1d5db;
        border-radius: 16px;
        padding: 30px;
        text-align: center;
    }

    .map-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 8px;
        margin-top: 8px;
    }

    .map-title {
        font-weight: 800;
        font-size: .8rem;
        margin-bottom: 6px;
    }

    .pedido-map {
        width: 100%;
        height: 90px;
        border: 0;
        border-radius: 12px;
    }

    .direccion-letras {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 7px 9px;
        margin-top: 7px;
    }

    .direccion-label {
        font-size: .72rem;
        font-weight: 800;
        color: #6b7280;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .direccion-texto {
        font-size: .82rem;
        color: #111827;
        font-weight: 700;
        line-height: 1.3;
    }

    .direccion-referencia {
        font-size: .78rem;
        color: #4b5563;
        margin-top: 4px;
        line-height: 1.3;
    }

    @media (max-width: 576px) {
        .pedido-actions .btn,
        .pedido-actions form {
            width: 100%;
        }

        .pedido-actions form button {
            width: 100%;
        }

        .pedido-total {
            width: 100%;
            text-align: left;
        }

        .pedido-map {
            height: 80px;
        }

        .direccion-texto {
            font-size: .8rem;
        }
    }
</style>

<div class="page-card">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa-solid fa-truck-fast text-warning"></i>
                Pedidos por enviar
            </h4>

            <p class="text-muted mb-0">
                Aquí aparecen los pedidos que están preparando o en camino.
            </p>
        </div>

        <a href="{{ route('vendedor.pedidos.index') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-list"></i>
            Todos los pedidos
        </a>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    {{-- LISTADO --}}
    @forelse($pedidos as $pedido)

        <div class="pedido-card">

            <div class="pedido-header">

                <div class="pedido-main">
                    <div class="pedido-code">
                        Pedido {{ $pedido->codigo }}
                    </div>

                    <div class="pedido-info">
                        <i class="fa-solid fa-user"></i>
                        Cliente:
                        <strong>{{ $pedido->cliente->name ?? 'Cliente no disponible' }}</strong>
                    </div>

                    <div class="pedido-info">
                        <i class="fa-solid fa-store"></i>
                        Local:
                        <strong>{{ $pedido->local->nombre ?? 'Local no disponible' }}</strong>
                    </div>

                    <div class="pedido-info">
                        @if($pedido->tipo_entrega === 'delivery')
                            <i class="fa-solid fa-motorcycle"></i>
                            Entrega:
                            <strong>A domicilio</strong>
                        @else
                            <i class="fa-solid fa-store"></i>
                            Entrega:
                            <strong>Recoger en local</strong>
                        @endif
                    </div>

                    @if($pedido->tipo_entrega === 'delivery' && $pedido->direccion)
                        <div class="pedido-info">
                            <i class="fa-solid fa-phone"></i>
                            Teléfono:
                            <strong>{{ $pedido->direccion->telefono ?? 'Sin teléfono' }}</strong>
                        </div>

                        @php
                            $direccionCompleta = trim(
                                ($pedido->direccion->calle ?? '') . ' ' .
                                ($pedido->direccion->numero ?? '') . ', ' .
                                ($pedido->direccion->colonia ?? '') . ', ' .
                                ($pedido->direccion->ciudad ?? '') . ', ' .
                                ($pedido->direccion->estado ?? '')
                            );

                            $direccionCompleta = trim($direccionCompleta, ' ,');
                        @endphp

                        @if($direccionCompleta)
                            <div class="map-box">
                                <div class="map-title">
                                    <i class="fa-solid fa-location-dot text-danger"></i>
                                    Ubicación de entrega
                                </div>

                                <iframe
                                    class="pedido-map"
                                    loading="lazy"
                                    allowfullscreen
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://www.google.com/maps/embed/v1/place?key={{ config('services.google_maps.key') }}&q={{ urlencode($direccionCompleta) }}">
                                </iframe>

                                <div class="direccion-letras">
                                    <div class="direccion-label">
                                        Dirección
                                    </div>

                                    <div class="direccion-texto">
                                        {{ $direccionCompleta }}
                                    </div>

                                    @if($pedido->direccion->referencia)
                                        <div class="direccion-referencia">
                                            <strong>Referencia:</strong>
                                            {{ $pedido->direccion->referencia }}
                                        </div>
                                    @endif
                                </div>

                                <a
                                    href="https://www.google.com/maps/search/?api=1&query={{ urlencode($direccionCompleta) }}"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-danger fw-bold w-100 mt-2">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                    Abrir en Google Maps
                                </a>
                            </div>
                        @endif
                    @endif
                </div>

                <div>
                    @if($pedido->estado_pedido === 'preparando')
                        <span class="badge bg-primary">
                            Preparando
                        </span>
                    @elseif($pedido->estado_pedido === 'en_camino')
                        <span class="badge bg-dark">
                            En camino
                        </span>
                    @endif
                </div>

                <div class="pedido-total">
                    Total<br>
                    ${{ number_format($pedido->total, 2) }}
                </div>
            </div>

            {{-- PRODUCTOS RESUMEN --}}
            @if($pedido->detalles && $pedido->detalles->count() > 0)
                <div class="table-responsive mt-2">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($pedido->detalles as $detalle)
                                <tr>
                                    <td>
                                        <strong>{{ $detalle->nombre_producto }}</strong>

                                        @if($detalle->nombre_variante)
                                            <small class="text-muted d-block">
                                                {{ $detalle->nombre_variante }}
                                            </small>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-secondary">
                                            {{ $detalle->cantidad }}
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        ${{ number_format($detalle->subtotal, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- BOTONES --}}
            <div class="pedido-actions">

                <a href="{{ route('vendedor.pedidos.ticket', $pedido->id) }}"
                   target="_blank"
                   class="btn btn-outline-dark fw-bold">
                    <i class="fa-solid fa-receipt"></i>
                    Ticket
                </a>

                <a href="{{ route('vendedor.pedidos.show', $pedido->id) }}"
                   class="btn btn-outline-secondary fw-bold">
                    <i class="fa-solid fa-eye"></i>
                    Detalle
                </a>

                @if($pedido->tipo_entrega === 'delivery' && $pedido->estado_pedido === 'preparando')
                    <form method="POST" action="{{ route('vendedor.pedidos.estado', $pedido->id) }}">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="estado_pedido" value="en_camino">
                        <input type="hidden" name="notas_vendedor" value="Tu pedido va en camino.">

                        <button type="submit"
                                class="btn btn-dark fw-bold"
                                onclick="return confirm('¿Marcar este pedido como en camino?')">
                            <i class="fa-solid fa-motorcycle"></i>
                            En camino
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('vendedor.pedidos.estado', $pedido->id) }}">
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="estado_pedido" value="entregado">
                    <input type="hidden" name="notas_vendedor" value="Pedido entregado.">

                    <button type="submit"
                            class="btn btn-success fw-bold"
                            onclick="return confirm('¿Confirmas que este pedido ya fue entregado? Después ya no se podrá modificar.')">
                        <i class="fa-solid fa-check"></i>
                        Entregado
                    </button>
                </form>

            </div>

        </div>

    @empty

        <div class="empty-box">
            <h5 class="fw-bold mb-2">
                <i class="fa-solid fa-circle-check text-success"></i>
                No hay pedidos por enviar
            </h5>

            <p class="text-muted mb-3">
                Cuando tengas pedidos en preparación o en camino aparecerán aquí.
            </p>

            <a href="{{ route('vendedor.pedidos.index') }}" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-list"></i>
                Ver todos los pedidos
            </a>
        </div>

    @endforelse

</div>

@endsection