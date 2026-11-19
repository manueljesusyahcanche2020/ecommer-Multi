@extends('layouts.dashboard')

@section('title', 'Notificaciones')
@section('page-title', 'Notificaciones')
@section('page-subtitle', 'Avisos importantes de tu cuenta')

@section('content')

<style>
    .notification-card {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 16px;
        background: #fff;
        margin-bottom: 12px;
        transition: .2s ease;
    }

    .notification-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 25px rgba(15, 23, 42, .08);
    }

    .notification-card.unread {
        background: #fff7ed;
        border-color: #fed7aa;
    }

    .notification-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        color: #374151;
        flex-shrink: 0;
    }

    .notification-card.unread .notification-icon {
        background: #fb923c;
        color: white;
    }

    .notification-actions {
        min-width: 120px;
    }
</style>

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Mis notificaciones</h4>
            <p class="text-muted mb-0">
                Avisos de pedidos, cambios de estado y mensajes importantes.
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-arrow-left"></i>
            Volver
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if($notificaciones->count() > 0)

        <div class="mb-3">
            <span class="badge bg-dark">
                {{ $notificaciones->count() }} notificación(es)
            </span>

            <span class="badge bg-danger">
                {{ $notificaciones->where('leida', false)->count() }} nueva(s)
            </span>
        </div>

        @foreach($notificaciones as $notificacion)
            <div class="notification-card {{ !$notificacion->leida ? 'unread' : '' }}">

                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">

                    <div class="d-flex gap-3">
                        <div class="notification-icon">
                            @if($notificacion->tipo === 'pedido')
                                <i class="fa-solid fa-receipt"></i>
                            @elseif($notificacion->tipo === 'cancelacion')
                                <i class="fa-solid fa-ban"></i>
                            @elseif($notificacion->tipo === 'estado')
                                <i class="fa-solid fa-truck-fast"></i>
                            @else
                                <i class="fa-solid fa-bell"></i>
                            @endif
                        </div>

                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h6 class="fw-bold mb-0">
                                    {{ $notificacion->titulo }}
                                </h6>

                                @if(!$notificacion->leida)
                                    <span class="badge bg-danger">Nuevo</span>
                                @endif
                            </div>

                            <p class="text-muted mb-1 mt-1">
                                {{ $notificacion->mensaje }}
                            </p>

                            <small class="text-muted">
                                <i class="fa-regular fa-clock"></i>
                                {{ $notificacion->created_at ? $notificacion->created_at->diffForHumans() : 'Sin fecha' }}
                            </small>
                        </div>
                    </div>

                    <div class="notification-actions text-end">
                        @if(!$notificacion->leida)
                            <form method="POST" action="{{ route('notificaciones.leer', $notificacion->id) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="btn btn-warning text-white fw-bold btn-sm w-100">
                                    <i class="fa-solid fa-eye"></i>
                                    Ver
                                </button>
                            </form>
                        @else
                            @if($notificacion->url)
                                <a href="{{ $notificacion->url }}" class="btn btn-outline-dark fw-bold btn-sm w-100">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    Abrir
                                </a>
                            @else
                                <span class="badge bg-secondary">Leída</span>
                            @endif
                        @endif
                    </div>

                </div>

            </div>
        @endforeach

    @else

        <div class="text-center py-5">
            <i class="fa-solid fa-bell-slash fa-3x text-muted mb-3"></i>

            <h5 class="fw-bold">No tienes notificaciones</h5>

            <p class="text-muted">
                Aquí aparecerán avisos cuando tengas nuevos pedidos o cambios importantes.
            </p>
        </div>

    @endif

</div>

@endsection