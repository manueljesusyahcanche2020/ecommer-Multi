@extends('layouts.dashboard')

@section('title', 'Panel admin')
@section('page-title', 'Panel admin')
@section('page-subtitle', 'Resumen general de la plataforma')

@section('content')

<div class="page-card">

    <h4 class="fw-bold mb-4">Resumen general</h4>

    <div class="row g-3">

        <div class="col-md-3">
            <div class="border rounded-4 p-3 h-100">
                <small class="text-muted">Usuarios</small>
                <h3 class="fw-bold mb-0">{{ $totalUsuarios }}</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="border rounded-4 p-3 h-100">
                <small class="text-muted">Locales</small>
                <h3 class="fw-bold mb-0">{{ $totalLocales }}</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="border rounded-4 p-3 h-100">
                <small class="text-muted">Productos</small>
                <h3 class="fw-bold mb-0">{{ $totalProductos }}</h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="border rounded-4 p-3 h-100">
                <small class="text-muted">Pedidos</small>
                <h3 class="fw-bold mb-0">{{ $totalPedidos }}</h3>
            </div>
        </div>

    </div>

    <hr class="my-4">

    <div class="row g-3">

        <div class="col-md-4">
            <div class="border rounded-4 p-3 h-100">
                <small class="text-muted">Pedidos pendientes</small>
                <h4 class="fw-bold text-warning mb-0">{{ $pedidosPendientes }}</h4>
            </div>
        </div>

        <div class="col-md-4">
            <div class="border rounded-4 p-3 h-100">
                <small class="text-muted">Pedidos entregados</small>
                <h4 class="fw-bold text-success mb-0">{{ $pedidosEntregados }}</h4>
            </div>
        </div>

        <div class="col-md-4">
            <div class="border rounded-4 p-3 h-100">
                <small class="text-muted">Pedidos cancelados</small>
                <h4 class="fw-bold text-danger mb-0">{{ $pedidosCancelados }}</h4>
            </div>
        </div>

    </div>

    <hr class="my-4">

    <div class="border rounded-4 p-3 bg-light">
        <small class="text-muted">Ventas entregadas</small>
        <h3 class="fw-bold mb-0">
            ${{ number_format($ventasTotales, 2) }}
        </h3>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-dark fw-bold">
            <i class="fa-solid fa-users"></i>
            Usuarios
        </a>

        <a href="{{ route('admin.locales.index') }}" class="btn btn-outline-dark fw-bold">
            <i class="fa-solid fa-store"></i>
            Locales
        </a>

        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-warning text-white fw-bold">
            <i class="fa-solid fa-receipt"></i>
            Pedidos
        </a>
    </div>

</div>

@endsection