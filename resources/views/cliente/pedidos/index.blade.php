@extends('layouts.dashboard')

@section('title', 'Mis pedidos')
@section('page-title', 'Mis pedidos')
@section('page-subtitle', 'Consulta el estado de tus pedidos')

@section('content')

<style>
    .pedido-card {
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        background: white;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .05);
        padding: 18px;
        margin-bottom: 16px;
    }

    .pedido-code {
        font-weight: 800;
        color: #111827;
    }

    .pedido-scroll {
        max-height: 720px;
        overflow-y: auto;
        padding-right: 6px;
    }

    .pedido-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .pedido-scroll::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 20px;
    }
</style>

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Historial de pedidos</h4>
            <p class="text-muted mb-0">
                Pedidos realizados:
                <span class="badge bg-dark">{{ $pedidos->count() }}</span>
            </p>
        </div>

        <a href="{{ route('cliente.catalogo.index') }}" class="btn btn-warning text-white fw-bold">
            <i class="fa-solid fa-store"></i>
            Seguir comprando
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

    @if($pedidos->count() > 0)

        <div class="pedido-scroll">

            @foreach($pedidos as $pedido)
                <div class="pedido-card">

                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                        <div>
                            <div class="pedido-code">
                                {{ $pedido->codigo }}
                            </div>

                            <div class="text-muted small">
                                Local: <strong>{{ $pedido->local->nombre ?? 'Local eliminado' }}</strong>
                            </div>

                            <div class="text-muted small">
                                Fecha: {{ $pedido->created_at?->format('d/m/Y H:i') }}
                            </div>
                        </div>

                        <div class="text-md-end">
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

                            <div class="mt-2">
                                <strong>${{ number_format($pedido->total, 2) }}</strong>
                            </div>
                        </div>

                    </div>

                    <hr>

                    <div class="row g-3 align-items-center">

                        <div class="col-md-3">
                            <small class="text-muted">Entrega</small>
                            <div class="fw-bold">
                                {{ $pedido->tipo_entrega == 'delivery' ? 'Domicilio' : 'Recoger' }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Pago</small>
                            <div class="fw-bold text-capitalize">
                                {{ $pedido->metodo_pago }}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Estado pago</small>
                            <div>
                                @if($pedido->estado_pago == 'pagado')
                                    <span class="badge bg-success">Pagado</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="d-flex gap-2 flex-wrap justify-content-md-end">

                                <a href="{{ route('cliente.pedidos.show', $pedido->id) }}"
                                   class="btn btn-sm btn-outline-dark fw-bold">
                                    <i class="fa-solid fa-eye"></i>
                                    Ver detalle
                                </a>

                                @if(in_array($pedido->estado_pedido, ['pendiente', 'aceptado']))
                                    <form method="POST"
                                          action="{{ route('cliente.pedidos.cancelar', $pedido->id) }}"
                                          onsubmit="return confirm('¿Seguro que quieres cancelar este pedido? Se devolverá el stock.');">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="btn btn-sm btn-outline-danger fw-bold">
                                            <i class="fa-solid fa-ban"></i>
                                            Cancelar
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </div>

                    </div>

                </div>
            @endforeach

        </div>

    @else

        <div class="text-center py-5">
            <i class="fa-solid fa-receipt fa-3x text-muted mb-3"></i>

            <h5 class="fw-bold">Todavía no tienes pedidos</h5>

            <p class="text-muted">
                Cuando confirmes un pedido, aparecerá aquí.
            </p>

            <a href="{{ route('cliente.catalogo.index') }}" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-store"></i>
                Ir al catálogo
            </a>
        </div>

    @endif

</div>

@endsection