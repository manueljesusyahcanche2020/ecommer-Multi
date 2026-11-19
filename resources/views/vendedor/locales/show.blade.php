@extends('layouts.dashboard')

@section('title', 'Ver local')
@section('page-title', $local->nombre)
@section('page-subtitle', 'Resumen del local')

@section('content')

<style>
    .box {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .box-soft {
        background: #f9fafb;
    }

    .section-title {
        font-weight: 800;
        margin-bottom: 12px;
    }

    .info-line {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .info-line:last-child {
        border-bottom: none;
    }

    .info-value {
        font-weight: 700;
        text-align: right;
    }

    .quick-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .zona-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .zona-row:last-child {
        border-bottom: none;
    }

    .zona-info strong {
        display: block;
    }

    .zona-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: end;
    }

    @media (max-width: 768px) {
        .zona-row {
            align-items: flex-start;
            flex-direction: column;
        }

        .zona-actions {
            justify-content: flex-start;
        }

        .info-line {
            flex-direction: column;
            gap: 2px;
        }

        .info-value {
            text-align: left;
        }
    }
</style>

<div class="page-card">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $local->nombre }}</h4>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if($local->estado_local == 'abierto')
                    <span class="badge bg-success">Abierto</span>
                @elseif($local->estado_local == 'pausado')
                    <span class="badge bg-warning text-dark">Pausado</span>
                @else
                    <span class="badge bg-secondary">Cerrado</span>
                @endif

                @if($local->permite_delivery)
                    <span class="badge bg-info text-dark">
                        <i class="fa-solid fa-motorcycle"></i>
                        Delivery
                    </span>
                @endif

                @if($local->permite_recoger)
                    <span class="badge bg-success">
                        <i class="fa-solid fa-store"></i>
                        Recoger
                    </span>
                @endif
            </div>

            <p class="text-muted mb-0 mt-2">
                {{ $local->descripcion ?? 'Sin descripción' }}
            </p>
        </div>

        <div class="quick-actions">
            <a href="{{ route('vendedor.locales.edit', $local->id) }}"
               class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-pen"></i>
                Editar
            </a>

            <a href="{{ route('vendedor.dashboard.locales') }}"
               class="btn btn-outline-secondary fw-bold">
                <i class="fa-solid fa-arrow-left"></i>
                Volver
            </a>
        </div>
    </div>

    {{-- ALERTAS --}}
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

    {{-- ACCIONES RÁPIDAS --}}
    <div class="box box-soft">
        <h5 class="section-title">
            <i class="fa-solid fa-bolt text-warning"></i>
            Acciones rápidas
        </h5>

        <div class="quick-actions">
            <a href="{{ route('vendedor.locales.productos.index', $local->id) }}"
               class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-box"></i>
                Productos
            </a>

            {{-- Cambia esta ruta cuando tengas el nombre real de categorías --}}
            <a href="#"
               class="btn btn-outline-dark fw-bold">
                <i class="fa-solid fa-tags"></i>
                Categorías
            </a>

            <a href="{{ route('vendedor.locales.horarios', $local->id) }}"
               class="btn btn-outline-secondary fw-bold">
                <i class="fa-solid fa-clock"></i>
                Horarios
            </a>
        </div>
    </div>

    <div class="row g-3">

        {{-- CONTACTO --}}
        <div class="col-md-6">
            <div class="box h-100">
                <h5 class="section-title">
                    <i class="fa-solid fa-phone text-warning"></i>
                    Contacto
                </h5>

                <div class="info-line">
                    <span class="text-muted">Teléfono</span>
                    <span class="info-value">{{ $local->telefono ?? 'Sin teléfono' }}</span>
                </div>

                <div class="info-line">
                    <span class="text-muted">WhatsApp</span>
                    <span class="info-value">{{ $local->whatsapp ?? 'Sin WhatsApp' }}</span>
                </div>
            </div>
        </div>

        {{-- UBICACIÓN --}}
        <div class="col-md-6">
            <div class="box h-100">
                <h5 class="section-title">
                    <i class="fa-solid fa-location-dot text-warning"></i>
                    Ubicación
                </h5>

                <div class="info-line">
                    <span class="text-muted">Ciudad</span>
                    <span class="info-value">
                        {{ $local->ciudad ?? 'Sin ciudad' }}
                        {{ $local->estado ? ', '.$local->estado : '' }}
                    </span>
                </div>

                <div class="info-line">
                    <span class="text-muted">Dirección</span>
                    <span class="info-value">{{ $local->direccion ?? 'Sin dirección' }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- CONFIGURACIÓN --}}
    <div class="box">
        <h5 class="section-title">
            <i class="fa-solid fa-sliders text-warning"></i>
            Configuración
        </h5>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="info-line">
                    <span class="text-muted">Mínimo</span>
                    <span class="info-value">
                        ${{ number_format($local->minimo_pedido ?? 0, 2) }}
                    </span>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-line">
                    <span class="text-muted">Envío base</span>
                    <span class="info-value">
                        ${{ number_format($local->costo_envio ?? 0, 2) }}
                    </span>
                </div>
            </div>

            <div class="col-md-4">
                <div class="info-line">
                    <span class="text-muted">Zonas</span>
                    <span class="info-value">
                        {{ $local->zonasEntrega->count() }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ZONAS DE ENTREGA --}}
    <div class="box mb-0">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="section-title mb-1">
                    <i class="fa-solid fa-map-location-dot text-warning"></i>
                    Zonas de entrega
                </h5>

                <p class="text-muted mb-0">
                    Lugares donde el local puede entregar.
                </p>
            </div>

            <span class="badge bg-dark fs-6">
                {{ $local->zonasEntrega->count() }} zona(s)
            </span>
        </div>

        @if($local->zonasEntrega->count() > 0)

            @foreach($local->zonasEntrega as $zona)
                <div class="zona-row">
                    <div class="zona-info">
                        <strong>
                            {{ $zona->ciudad }}
                            {{ $zona->estado ? ', '.$zona->estado : '' }}
                        </strong>

                        <small class="text-muted">
                            @if(($zona->costo_envio ?? 0) > 0)
                                Envío: ${{ number_format($zona->costo_envio, 2) }}
                            @else
                                Envío gratis
                            @endif
                        </small>

                        <div class="mt-1">
                            @if($zona->activo)
                                <span class="badge bg-success">Activa</span>
                            @else
                                <span class="badge bg-secondary">Inactiva</span>
                            @endif
                        </div>
                    </div>

                    <div class="zona-actions">
                        <form method="POST"
                              action="{{ route('vendedor.locales.zonas.estado', [$local->id, $zona->id]) }}">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="btn btn-sm {{ $zona->activo ? 'btn-outline-warning' : 'btn-outline-success' }} fw-bold">
                                @if($zona->activo)
                                    <i class="fa-solid fa-pause"></i>
                                    Desactivar
                                @else
                                    <i class="fa-solid fa-play"></i>
                                    Activar
                                @endif
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route('vendedor.locales.zonas.destroy', [$local->id, $zona->id]) }}"
                              onsubmit="return confirm('¿Seguro que quieres eliminar esta zona de entrega?');">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="btn btn-sm btn-outline-danger fw-bold">
                                <i class="fa-solid fa-trash"></i>
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach

        @else
            <div class="alert alert-light border mb-0">
                <i class="fa-solid fa-circle-info"></i>
                Este local todavía no tiene zonas de entrega registradas.
            </div>
        @endif
    </div>

</div>

@endsection