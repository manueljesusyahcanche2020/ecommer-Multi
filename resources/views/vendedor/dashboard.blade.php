@extends('layouts.dashboard')

@section('title', 'Panel cliente')
@section('page-title', 'Panel cliente')
@section('page-subtitle', 'Explora locales, productos y pedidos')

@section('content')

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="icon">
                <i class="fa-solid fa-shop"></i>
            </div>
            <h3>Catálogo</h3>
            <p>Explora productos y locales</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="icon">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <h3>{{ $totalPedidos ?? 0 }}</h3>
            <p>Pedidos realizados</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="icon">
                <i class="fa-solid fa-clock"></i>
            </div>
            <h3>{{ $pedidosPendientes ?? 0 }}</h3>
            <p>Pedidos en proceso</p>
        </div>
    </div>
</div>

<div class="page-card mb-4">
    <h4 class="fw-bold mb-3">Hola, {{ Auth::user()->name }}</h4>

    <p class="text-muted mb-4">
        Desde aquí puedes explorar locales, agregar productos al carrito y revisar tus pedidos.
    </p>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('cliente.catalogo.index') }}" class="btn btn-warning text-white fw-bold">
            <i class="fa-solid fa-shop"></i>
            Ver catálogo
        </a>

        <a href="{{ route('cliente.carrito.index') }}" class="btn btn-outline-dark fw-bold">
            <i class="fa-solid fa-cart-shopping"></i>
            Ver carrito
        </a>

        <a href="{{ route('cliente.pedidos.ver') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-receipt"></i>
            Mis pedidos
        </a>
    </div>
</div>

<div class="page-card">
    <h5 class="fw-bold mb-3">Últimos pedidos</h5>

    @if(isset($ultimosPedidos) && $ultimosPedidos->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Local</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ultimosPedidos as $pedido)
                        <tr>
                            <td>{{ $pedido->codigo }}</td>
                            <td>{{ $pedido->local->nombre ?? 'Sin local' }}</td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    {{ $pedido->estado_pedido }}
                                </span>
                            </td>
                            <td>${{ number_format($pedido->total, 2) }}</td>
                            <td>{{ $pedido->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-muted mb-0">Todavía no tienes pedidos.</p>
    @endif
</div>

@endsection