@extends('layouts.dashboard')

@section('title', 'Sin permisos')
@section('page-title', 'Acceso denegado')
@section('page-subtitle', 'No tienes permiso para entrar a esta sección')

@section('content')

<div class="page-card text-center py-5">
    <i class="fa-solid fa-lock fa-3x text-warning mb-3"></i>

    <h4 class="fw-bold">No tienes permisos</h4>

    <p class="text-muted">
        Esta sección no está disponible para tu tipo de cuenta.
    </p>

    <a href="{{ route('dashboard') }}" class="btn btn-warning text-white fw-bold">
        <i class="fa-solid fa-house"></i>
        Ir a mi panel
    </a>
</div>

@endsection