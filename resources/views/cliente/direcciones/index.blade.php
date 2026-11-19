@extends('layouts.dashboard')

@section('title', 'Mis direcciones')
@section('page-title', 'Direcciones')
@section('page-subtitle', 'Administra tus direcciones de entrega')

@section('content')

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Mis direcciones</h4>
            <p class="text-muted mb-0">
                Direcciones registradas:
                <span class="badge bg-dark">{{ $direcciones->count() }}</span>
            </p>
        </div>

        <a href="{{ route('cliente.direcciones.create') }}" class="btn btn-warning text-white fw-bold">
            <i class="fa-solid fa-plus"></i>
            Nueva dirección
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

    @if($direcciones->count() > 0)
        <div class="row g-4">
            @foreach($direcciones as $direccion)
                <div class="col-md-6">
                    <div class="border rounded-4 p-3 h-100">

                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="fw-bold mb-1">
                                    {{ $direccion->nombre_recibe }}
                                </h5>

                                <small class="text-muted">
                                    {{ $direccion->telefono ?? 'Sin teléfono' }}
                                </small>
                            </div>

                            @if($direccion->principal)
                                <span class="badge bg-success">Principal</span>
                            @endif
                        </div>

                        <p class="mb-1">
                            {{ $direccion->calle }}
                            {{ $direccion->numero ? '#'.$direccion->numero : '' }}
                        </p>

                        @if($direccion->cruzamientos)
                            <p class="text-muted mb-1">
                                Cruzamientos: {{ $direccion->cruzamientos }}
                            </p>
                        @endif

                        <p class="text-muted mb-1">
                            {{ $direccion->colonia ? $direccion->colonia.', ' : '' }}
                            {{ $direccion->ciudad }},
                            {{ $direccion->estado }}
                            {{ $direccion->codigo_postal ? ' CP '.$direccion->codigo_postal : '' }}
                        </p>

                        <p class="text-muted mb-2">
                            Referencia: {{ $direccion->referencia ?? 'Sin referencia' }}
                        </p>

                        @if($direccion->latitud && $direccion->longitud)
                            <a
                                href="https://www.google.com/maps?q={{ $direccion->latitud }},{{ $direccion->longitud }}"
                                target="_blank"
                                class="btn btn-sm btn-outline-secondary fw-bold mb-2">
                                <i class="fa-solid fa-map-location-dot"></i>
                                Ver en Maps
                            </a>
                        @endif

                        <hr>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('cliente.direcciones.show', $direccion->id) }}"
                               class="btn btn-sm btn-outline-dark fw-bold">
                                <i class="fa-solid fa-eye"></i>
                                Ver
                            </a>

                            <a href="{{ route('cliente.direcciones.edit', $direccion->id) }}"
                               class="btn btn-sm btn-warning text-white fw-bold">
                                <i class="fa-solid fa-pen"></i>
                                Editar
                            </a>

                            <form method="POST"
                                  action="{{ route('cliente.direcciones.destroy', $direccion->id) }}"
                                  onsubmit="return confirm('¿Seguro que quieres eliminar esta dirección?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-outline-danger fw-bold">
                                    <i class="fa-solid fa-trash"></i>
                                    Eliminar
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <i class="fa-solid fa-location-dot fa-3x text-muted mb-3"></i>

            <h5 class="fw-bold">No tienes direcciones registradas</h5>

            <p class="text-muted">
                Agrega una dirección para pedidos a domicilio.
            </p>

            <a href="{{ route('cliente.direcciones.create') }}" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-plus"></i>
                Crear dirección
            </a>
        </div>
    @endif

</div>

@endsection