@extends('layouts.dashboard')

@section('title', 'Pedidos')
@section('page-title', 'Pedidos')
@section('page-subtitle', 'Administra los pedidos recibidos')

@section('content')

<style>
    .filter-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .pedidos-scroll {
        max-height: 680px;
        overflow-y: auto;
        overflow-x: auto;
        padding-right: 6px;
    }

    .pedidos-scroll::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .pedidos-scroll::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 20px;
    }
</style>

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Pedidos recibidos</h4>
            <p class="text-muted mb-0">
                Pedidos encontrados:
                <span class="badge bg-dark">{{ $pedidos->count() }}</span>
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i>
            {{ session('success') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <form method="GET" action="{{ route('vendedor.pedidos.index') }}" class="filter-card">
        <div class="row g-3 align-items-end">

            <div class="col-md-4">
                <label class="form-label fw-bold">Buscar</label>
                <input
                    type="text"
                    name="buscar"
                    class="form-control"
                    placeholder="Código, cliente o teléfono"
                    value="{{ request('buscar') }}"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Local</label>
                <select name="local_id" class="form-control">
                    <option value="">Todos los locales</option>

                    @foreach($locales as $local)
                        <option value="{{ $local->id }}" {{ request('local_id') == $local->id ? 'selected' : '' }}>
                            {{ $local->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Estado</label>
                <select name="estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="aceptado" {{ request('estado') == 'aceptado' ? 'selected' : '' }}>Aceptado</option>
                    <option value="preparando" {{ request('estado') == 'preparando' ? 'selected' : '' }}>Preparando</option>
                    <option value="en_camino" {{ request('estado') == 'en_camino' ? 'selected' : '' }}>En camino</option>
                    <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                    <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-warning text-white fw-bold w-100">
                    <i class="fa-solid fa-filter"></i>
                    Filtrar
                </button>
            </div>

            <div class="col-12">
                <a href="{{ route('vendedor.pedidos.index') }}" class="btn btn-sm btn-outline-secondary fw-bold">
                    Limpiar filtros
                </a>
            </div>

        </div>
    </form>

    @if($pedidos->count() > 0)

        <div class="pedidos-scroll">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th>Local</th>
                        <th>Entrega</th>
                        <th>Pago</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($pedidos as $pedido)
                        <tr>
                            <td>
                                <strong>{{ $pedido->codigo }}</strong>
                            </td>

                            <td>
                                <strong>{{ $pedido->cliente->name ?? 'Cliente eliminado' }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $pedido->cliente->telefono ?? 'Sin teléfono' }}
                                </small>
                            </td>

                            <td>{{ $pedido->local->nombre ?? 'Sin local' }}</td>

                            <td>
                                @if($pedido->tipo_entrega == 'delivery')
                                    <span class="badge bg-info text-dark">Domicilio</span>
                                @else
                                    <span class="badge bg-secondary">Recoger</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $pedido->metodo_pago }}
                                </span>
                                <br>
                                @if($pedido->estado_pago == 'pagado')
                                    <span class="badge bg-success">Pagado</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                @endif
                            </td>

                            <td>
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
                            </td>

                            <td>
                                <strong>${{ number_format($pedido->total, 2) }}</strong>
                            </td>

                            <td>
                                {{ $pedido->created_at?->format('d/m/Y H:i') }}
                            </td>

                            <td>
                                <a href="{{ route('vendedor.pedidos.show', $pedido->id) }}"
                                   class="btn btn-sm btn-outline-dark fw-bold">
                                    <i class="fa-solid fa-eye"></i>
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @else

        <div class="text-center py-5">
            <i class="fa-solid fa-receipt fa-3x text-muted mb-3"></i>

            <h5 class="fw-bold">No tienes pedidos todavía</h5>

            <p class="text-muted">
                Cuando un cliente compre productos de tus locales, aparecerán aquí.
            </p>
        </div>

    @endif

</div>

@endsection