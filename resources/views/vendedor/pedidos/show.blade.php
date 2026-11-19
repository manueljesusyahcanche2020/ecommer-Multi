@extends('layouts.dashboard')

@section('title', 'Detalle del pedido')
@section('page-title', 'Detalle del pedido')
@section('page-subtitle', 'Vista simple para atender el pedido')

@section('content')

<style>
    .box {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .box-total {
        background: #fff7ed;
        border-color: #fed7aa;
    }

    .label-mini {
        color: #6b7280;
        font-size: .82rem;
        margin-bottom: 2px;
        display: block;
    }

    .info-line {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid #f1f5f9;
        padding: 8px 0;
    }

    .info-line:last-child {
        border-bottom: none;
    }

    .info-value {
        font-weight: 700;
        text-align: right;
    }

    .section-title {
        font-weight: 800;
        margin-bottom: 12px;
    }

    .product-title {
        font-weight: 800;
    }

    .badge-light {
        background: #f9fafb;
        color: #111827;
        border: 1px solid #e5e7eb;
    }
</style>

<div class="page-card">

    {{-- ENCABEZADO --}}
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h4 class="fw-bold mb-1">Pedido {{ $pedido->codigo }}</h4>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <p class="text-muted mb-0">
                    {{ $pedido->local->nombre ?? 'Local no disponible' }}
                </p>

                @if($pedido->tipo_entrega === 'delivery')
                    <span class="badge badge-light">
                        <i class="fa-solid fa-motorcycle me-1"></i>
                        A domicilio
                    </span>
                @else
                    <span class="badge badge-light">
                        <i class="fa-solid fa-store me-1"></i>
                        Recoger
                    </span>
                @endif

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
            </div>
        </div>

        <a href="{{ route('vendedor.pedidos.index') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-arrow-left"></i>
            Volver
        </a>
    </div>

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Revisa los datos:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif
{{-- ACTUALIZAR PEDIDO --}}
    @if(!in_array($pedido->estado_pedido, ['cancelado', 'entregado']))
        <div class="box">
            <h5 class="section-title">
                <i class="fa-solid fa-pen-to-square text-warning"></i>
                Actualizar pedido y pago
            </h5>

            <form method="POST" action="{{ route('vendedor.pedidos.estado', $pedido->id) }}">
                @csrf
                @method('PATCH')

                <div class="row g-3 align-items-end">
                    
                    {{-- Estado del Pedido --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Estado del pedido</label>
                        <select name="estado_pedido" class="form-control" required>
                            <option value="pendiente" {{ $pedido->estado_pedido == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="aceptado" {{ $pedido->estado_pedido == 'aceptado' ? 'selected' : '' }}>Aceptado</option>
                            <option value="preparando" {{ $pedido->estado_pedido == 'preparando' ? 'selected' : '' }}>Preparando</option>
                            @if($pedido->tipo_entrega === 'delivery')
                                <option value="en_camino" {{ $pedido->estado_pedido == 'en_camino' ? 'selected' : '' }}>En camino</option>
                            @endif
                            <option value="entregado" {{ $pedido->estado_pedido == 'entregado' ? 'selected' : '' }}>Entregado</option>
                            <option value="cancelado" {{ $pedido->estado_pedido == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>

                    {{-- Estado de Pago (NUEVO) --}}
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Estado de pago</label>
                        <select name="estado_pago" class="form-control" required>
                            <option value="pendiente" {{ $pedido->estado_pago == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="pagado" {{ $pedido->estado_pago == 'pagado' ? 'selected' : '' }}>Pagado</option>
                            <option value="fallido" {{ $pedido->estado_pago == 'fallido' ? 'selected' : '' }}>Fallido</option>
                            <option value="reembolsado" {{ $pedido->estado_pago == 'reembolsado' ? 'selected' : '' }}>Reembolsado</option>
                        </select>
                    </div>

                    {{-- Mensaje al cliente --}}
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Mensaje al cliente</label>
                        <input
                            type="text"
                            name="notas_vendedor"
                            class="form-control"
                            placeholder="Ej. Listo en 15 minutos"
                            value="{{ old('notas_vendedor', $pedido->notas_vendedor) }}"
                        >
                    </div>

                    {{-- Botón Guardar --}}
                    <div class="col-md-2">
                        <button
                            type="submit"
                            class="btn btn-warning text-white fw-bold w-100"
                            onclick="return confirmarCambioEstado(this.form);"
                        >
                            Guardar
                        </button>
                    </div>
                </div>

                <small class="text-muted d-block mt-2">
                    * No podrás marcar un pedido como <b>Entregado</b> si el estado de pago no se encuentra en <b>Pagado</b>.
                </small>
            </form>
        </div>
    @else
        <div class="alert {{ $pedido->estado_pedido === 'entregado' ? 'alert-success' : 'alert-danger' }}">
            @if($pedido->estado_pedido === 'entregado')
                <strong>Pedido entregado.</strong> Ya no se puede modificar.
            @else
                <strong>Pedido cancelado.</strong> Ya no se puede modificar.
            @endif
        </div>
    @endif
        <div class="row g-3">

        {{-- ENTREGA SIMPLE --}}
        <div class="col-md-6">
            <div class="box h-100">
                <h5 class="section-title">
                    <i class="fa-solid fa-user text-warning"></i>
                    Entrega
                </h5>

                <div class="info-line">
                    <span class="text-muted">Cliente</span>
                    <span class="info-value">
                        {{ $pedido->cliente->name ?? 'Cliente no disponible' }}
                    </span>
                </div>

                @if($pedido->tipo_entrega === 'delivery')
                    @if($pedido->direccion)
                        <div class="info-line">
                            <span class="text-muted">Teléfono</span>
                            <span class="info-value">
                                {{ $pedido->direccion->telefono ?? 'Sin teléfono' }}
                            </span>
                        </div>

                        <div class="info-line">
                            <span class="text-muted">Entrega</span>
                            <span class="info-value">A domicilio</span>
                        </div>

                        <a href="{{ route('vendedor.pedidos.ticket', $pedido->id) }}"
                           target="_blank"
                           class="btn btn-outline-dark fw-bold w-100 mt-3">
                            <i class="fa-solid fa-receipt"></i>
                            Ver ticket de entrega
                        </a>
                    @else
                        <div class="alert alert-warning mb-0 mt-3">
                            Pedido a domicilio sin dirección guardada.
                        </div>
                    @endif
                @else
                    <div class="info-line">
                        <span class="text-muted">Entrega</span>
                        <span class="info-value">Recoger en local</span>
                    </div>

                    <a href="{{ route('vendedor.pedidos.ticket', $pedido->id) }}"
                       target="_blank"
                       class="btn btn-outline-dark fw-bold w-100 mt-3">
                        <i class="fa-solid fa-receipt"></i>
                        Ver ticket
                    </a>
                @endif
            </div>
        </div>

        {{-- NOTAS --}}
        <div class="col-md-6">
            <div class="box h-100">
                <h5 class="section-title">
                    <i class="fa-solid fa-message text-warning"></i>
                    Notas
                </h5>

                <span class="label-mini">Cliente</span>
                <p class="mb-3">
                    {{ $pedido->notas_cliente ?? 'Sin nota del cliente.' }}
                </p>

                <span class="label-mini">Mensaje del local</span>
                <p class="mb-0">
                    {{ $pedido->notas_vendedor ?? 'Sin mensaje para el cliente.' }}
                </p>
            </div>
        </div>

    </div>

    {{-- PRODUCTOS --}}
    <div class="box">
        <h5 class="section-title">
            <i class="fa-solid fa-box text-warning"></i>
            Productos
        </h5>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($pedido->detalles as $detalle)
                        <tr>
                            <td>
                                <div class="product-title">
                                    {{ $detalle->nombre_producto }}
                                </div>

                                @if($detalle->nombre_variante)
                                    <small class="text-muted">
                                        {{ $detalle->nombre_variante }}
                                    </small>
                                @endif

                                <small class="text-muted d-block">
                                    ${{ number_format($detalle->precio_unitario, 2) }} c/u
                                </small>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-secondary">
                                    {{ $detalle->cantidad }}
                                </span>
                            </td>

                            <td class="text-end">
                                <strong>
                                    ${{ number_format($detalle->subtotal, 2) }}
                                </strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGO --}}
    <div class="box box-total mb-0">
        <h5 class="section-title">
            <i class="fa-solid fa-money-bill text-warning"></i>
            Pago
        </h5>

        <div class="info-line">
            <span>Método</span>
            <strong>{{ ucfirst($pedido->metodo_pago) }}</strong>
        </div>

        <div class="info-line">
            <span>Subtotal</span>
            <strong>${{ number_format($pedido->subtotal, 2) }}</strong>
        </div>

        <div class="info-line">
            <span>Envío</span>
            <strong>${{ number_format($pedido->costo_envio, 2) }}</strong>
        </div>

        @if($pedido->descuento > 0)
            <div class="info-line">
                <span>Descuento</span>
                <strong>-${{ number_format($pedido->descuento, 2) }}</strong>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="fw-bold">Total</span>
            <h3 class="fw-bold mb-0">
                ${{ number_format($pedido->total, 2) }}
            </h3>
        </div>
    </div>

</div>

<script>
    function confirmarCambioEstado(form) {
        const estado = form.querySelector('[name="estado_pedido"]').value;

        if (estado === 'cancelado') {
            return confirm('¿Seguro que quieres cancelar este pedido? El stock se devolverá automáticamente.');
        }

        if (estado === 'entregado') {
            return confirm('¿Confirmas que este pedido ya fue entregado? Después ya no se podrá modificar.');
        }

        return true;
    }
</script>

@endsection