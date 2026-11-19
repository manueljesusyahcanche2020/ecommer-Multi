@extends('layouts.dashboard')

@section('title', 'Ver dirección')
@section('page-title', 'Detalle de dirección')
@section('page-subtitle', 'Información completa de tu dirección')

@section('content')

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">{{ $direccion->nombre_recibe }}</h4>
            <p class="text-muted mb-0">
                Información de la dirección registrada.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('cliente.direcciones.index') }}" class="btn btn-outline-secondary fw-bold">
                <i class="fa-solid fa-arrow-left"></i>
                Volver
            </a>

            <a href="{{ route('cliente.direcciones.edit', $direccion->id) }}" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-pen"></i>
                Editar
            </a>
        </div>
    </div>

    <div class="row g-3">

        <div class="col-md-6">
            <div class="border rounded-4 p-3">
                <small class="text-muted">Nombre de quien recibe</small>
                <h6 class="mb-0">{{ $direccion->nombre_recibe }}</h6>
            </div>
        </div>

        <div class="col-md-6">
            <div class="border rounded-4 p-3">
                <small class="text-muted">Teléfono</small>
                <h6 class="mb-0">{{ $direccion->telefono ?? 'Sin teléfono' }}</h6>
            </div>
        </div>

        <div class="col-md-8">
            <div class="border rounded-4 p-3">
                <small class="text-muted">Calle</small>
                <h6 class="mb-0">{{ $direccion->calle ?? 'Sin calle' }}</h6>
            </div>
        </div>

        <div class="col-md-4">
            <div class="border rounded-4 p-3">
                <small class="text-muted">Número</small>
                <h6 class="mb-0">{{ $direccion->numero ?? 'Sin número' }}</h6>
            </div>
        </div>

        <div class="col-12">
            <div class="border rounded-4 p-3">
                <small class="text-muted">Cruzamientos</small>
                <h6 class="mb-0">{{ $direccion->cruzamientos ?? 'Sin cruzamientos' }}</h6>
            </div>
        </div>

        <div class="col-md-6">
            <div class="border rounded-4 p-3">
                <small class="text-muted">Colonia</small>
                <h6 class="mb-0">{{ $direccion->colonia ?? 'Sin colonia' }}</h6>
            </div>
        </div>

        <div class="col-md-6">
            <div class="border rounded-4 p-3">
                <small class="text-muted">Código postal</small>
                <h6 class="mb-0">{{ $direccion->codigo_postal ?? 'Sin código postal' }}</h6>
            </div>
        </div>

        <div class="col-md-6">
            <div class="border rounded-4 p-3">
                <small class="text-muted">Ciudad</small>
                <h6 class="mb-0">{{ $direccion->ciudad ?? 'Sin ciudad' }}</h6>
            </div>
        </div>

        <div class="col-md-6">
            <div class="border rounded-4 p-3">
                <small class="text-muted">Estado</small>
                <h6 class="mb-0">{{ $direccion->estado ?? 'Sin estado' }}</h6>
            </div>
        </div>

        <div class="col-12">
            <div class="border rounded-4 p-3">
                <small class="text-muted">Referencia</small>
                <h6 class="mb-0">{{ $direccion->referencia ?? 'Sin referencia' }}</h6>
            </div>
        </div>

        <div class="col-md-6">
            <div class="border rounded-4 p-3">
                <small class="text-muted">Dirección principal</small>
                <h6 class="mb-0">
                    @if($direccion->principal)
                        <span class="badge bg-success">Sí</span>
                    @else
                        <span class="badge bg-secondary">No</span>
                    @endif
                </h6>
            </div>
        </div>

        <div class="col-md-6">
            <div class="border rounded-4 p-3">
                <small class="text-muted">Coordenadas</small>
                <h6 class="mb-0">
                    @if($direccion->latitud && $direccion->longitud)
                        {{ $direccion->latitud }}, {{ $direccion->longitud }}
                    @else
                        Sin coordenadas
                    @endif
                </h6>
            </div>
        </div>

    </div>

    @if($direccion->latitud && $direccion->longitud)
        <div class="mt-4">
            <a
                href="https://www.google.com/maps?q={{ $direccion->latitud }},{{ $direccion->longitud }}"
                target="_blank"
                class="btn btn-outline-dark fw-bold">
                <i class="fa-solid fa-map-location-dot"></i>
                Ver en Google Maps
            </a>
        </div>
    @endif

</div>

@endsection