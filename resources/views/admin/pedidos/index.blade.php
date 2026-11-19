@extends('layouts.dashboard')

@section('title', 'Pedidos')
@section('page-title', 'Pedidos')
@section('page-subtitle', 'Administración general de pedidos')

@section('content')

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Pedidos registrados</h4>

        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-arrow-left"></i>
            Volver
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Local</th>
                    <th>Entrega</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th class="text-end">Acción</th>
                </tr>
            </thead>

            <tbody>
                @forelse($pedidos as $pedido)
                    <tr>
                        <td>
                            <strong>{{ $pedido->codigo }}</strong>
                        </td>

                        <td>
                            {{ $pedido->cliente->name ?? 'Sin cliente' }}
                        </td>

                        <td>
                            {{ $pedido->local->nombre ?? 'Sin local' }}
                        </td>

                        <td>
                            {{ $pedido->tipo_entrega === 'delivery' ? 'Domicilio' : 'Recoger' }}
                        </td>

                        <td>
                            <strong>${{ number_format($pedido->total, 2) }}</strong>
                        </td>

                        <td>
                            @if($pedido->estado_pedido === 'pendiente')
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            @elseif($pedido->estado_pedido === 'aceptado')
                                <span class="badge bg-info text-dark">Aceptado</span>
                            @elseif($pedido->estado_pedido === 'preparando')
                                <span class="badge bg-primary">Preparando</span>
                            @elseif($pedido->estado_pedido === 'en_camino')
                                <span class="badge bg-dark">En camino</span>
                            @elseif($pedido->estado_pedido === 'entregado')
                                <span class="badge bg-success">Entregado</span>
                            @else
                                <span class="badge bg-danger">Cancelado</span>
                            @endif
                        </td>

                        <td>
                            {{ $pedido->created_at ? $pedido->created_at->format('d/m/Y H:i') : 'Sin fecha' }}
                        </td>

                        <td class="text-end">
                            <a href="{{ route('admin.pedidos.show', $pedido->id) }}"
                               class="btn btn-sm btn-outline-dark fw-bold">
                                <i class="fa-solid fa-eye"></i>
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            No hay pedidos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

</div>

@endsection