@extends('layouts.dashboard')

@section('title', 'Mis locales')
@section('page-title', 'Mis locales')
@section('page-subtitle', 'Administra tus locales')

@section('content')

<style>
    .local-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 16px;
        height: 100%;
    }

    .local-logo {
        width: 58px;
        height: 58px;
        object-fit: cover;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
    }

    .local-logo-empty {
        width: 58px;
        height: 58px;
        border-radius: 16px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .local-title {
        font-weight: 800;
        margin-bottom: 2px;
    }

    .local-text {
        color: #6b7280;
        font-size: .9rem;
        margin-bottom: 0;
    }

    .actions-row {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 14px;
    }

    .more-actions {
        border-top: 1px solid #f1f5f9;
        margin-top: 12px;
        padding-top: 12px;
        display: none;
    }

    .empty-box {
        text-align: center;
        padding: 48px 16px;
        border: 1px dashed #d1d5db;
        border-radius: 18px;
        background: #fff;
    }
</style>

<div class="page-card">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Locales registrados</h4>
            <p class="text-muted mb-0">Gestiona tus locales de forma rápida.</p>
        </div>

        <a href="{{ route('vendedor.locales.create') }}"
           class="btn btn-warning text-white fw-bold">
            <i class="fa-solid fa-plus"></i>
            Nuevo local
        </a>
    </div>

    {{-- MENSAJES --}}
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

    @if($locales->count() > 0)

        <div class="row g-3">
            @foreach($locales as $local)
                <div class="col-md-6 col-xl-4">
                    <div class="local-card">

                        {{-- INFO PRINCIPAL --}}
                        <div class="d-flex gap-3 align-items-start">
                            <div>
                                @if($local->logo)
                                    <img
                                        src="{{ asset('storage/' . $local->logo) }}"
                                        alt="{{ $local->nombre }}"
                                        class="local-logo"
                                    >
                                @else
                                    <div class="local-logo-empty">
                                        <i class="fa-solid fa-store text-warning"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-grow-1">
                                <h5 class="local-title">{{ $local->nombre }}</h5>

                                <p class="local-text">
                                    {{ $local->ciudad ?? 'Sin ciudad' }}
                                </p>

                                <div class="d-flex gap-2 flex-wrap mt-2">
                                    @if($local->estado_local == 'abierto')
                                        <span class="badge bg-success">Abierto</span>
                                    @elseif($local->estado_local == 'pausado')
                                        <span class="badge bg-warning text-dark">Pausado</span>
                                    @else
                                        <span class="badge bg-secondary">Cerrado</span>
                                    @endif

                                    @if($local->activo)
                                        <span class="badge bg-light text-dark border">Activo</span>
                                    @else
                                        <span class="badge bg-danger">No activo</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ACCIONES PRINCIPALES --}}
                        <div class="actions-row">
                            <a href="{{ route('vendedor.locales.productos.index', $local->id) }}"
                               class="btn btn-sm btn-warning text-white fw-bold">
                                <i class="fa-solid fa-box"></i>
                                Productos
                            </a>

                            <a href="{{ route('vendedor.locales.show', $local->id) }}"
                               class="btn btn-sm btn-outline-dark fw-bold">
                                <i class="fa-solid fa-eye"></i>
                                Ver
                            </a>

                            <a href="{{ route('vendedor.locales.edit', $local->id) }}"
                               class="btn btn-sm btn-outline-primary fw-bold">
                                <i class="fa-solid fa-pen"></i>
                                Editar
                            </a>

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary fw-bold"
                                    onclick="toggleActions('acciones-{{ $local->id }}', this)">
                                Más
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                        </div>

                        {{-- ACCIONES SECUNDARIAS --}}
                        <div id="acciones-{{ $local->id }}" class="more-actions">
                            <div class="d-flex flex-wrap gap-2">

                                <form method="POST"
                                      action="{{ route('vendedor.locales.pausar', $local->id) }}">
                                    @csrf
                                    @method('PATCH')

                                    @if($local->estado_local == 'pausado')
                                        <button type="submit"
                                                class="btn btn-sm btn-success fw-bold">
                                            <i class="fa-solid fa-play"></i>
                                            Activar
                                        </button>
                                    @else
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-warning fw-bold">
                                            <i class="fa-solid fa-pause"></i>
                                            Pausar
                                        </button>
                                    @endif
                                </form>

                                <form method="POST"
                                      action="{{ route('vendedor.locales.destroy', $local->id) }}"
                                      onsubmit="return confirm('¿Seguro que quieres eliminar este local? Sus productos quedarán no públicos.');">
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

                    </div>
                </div>
            @endforeach
        </div>

    @else

        {{-- ESTADO VACÍO --}}
        <div class="empty-box">
            <i class="fa-solid fa-store fa-3x text-muted mb-3"></i>

            <h5 class="fw-bold">Todavía no tienes locales</h5>

            <p class="text-muted">Crea tu primer local para comenzar a vender.</p>

            <a href="{{ route('vendedor.locales.create') }}"
               class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-plus"></i>
                Crear local
            </a>
        </div>

    @endif

</div>

<script>
    function toggleActions(id, button) {
        const box = document.getElementById(id);
        const icon = button.querySelector('.fa-chevron-down, .fa-chevron-up');

        if (!box) return;

        const isHidden = box.style.display === 'none' || box.style.display === '';

        box.style.display = isHidden ? 'block' : 'none';

        if (icon) {
            icon.classList.toggle('fa-chevron-down', !isHidden);
            icon.classList.toggle('fa-chevron-up', isHidden);
        }
    }
</script>

@endsection