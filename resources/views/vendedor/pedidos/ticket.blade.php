<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket {{ $pedido->codigo }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 20px;
            color: #111827;
        }

        .ticket {
            max-width: 380px;
            margin: auto;
            background: #fff;
            padding: 18px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }

        .center {
            text-align: center;
        }

        h2, h3, p {
            margin: 0;
        }

        .muted {
            color: #6b7280;
            font-size: 13px;
        }

        .section {
            border-top: 1px dashed #d1d5db;
            margin-top: 14px;
            padding-top: 14px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .bold {
            font-weight: bold;
        }

        .product {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .btn-print {
            display: block;
            width: 100%;
            margin-top: 16px;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #facc15;
            font-weight: bold;
            cursor: pointer;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .ticket {
                max-width: none;
                border: none;
                border-radius: 0;
            }

            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="ticket">

    <div class="center">
        <h2>{{ $pedido->local->nombre ?? 'Local' }}</h2>
        <p class="muted">Ticket de entrega</p>
        <p class="muted">{{ $pedido->codigo }}</p>
    </div>

    <div class="section">
        <div class="row">
            <span>Estado</span>
            <span class="bold">{{ ucfirst(str_replace('_', ' ', $pedido->estado_pedido)) }}</span>
        </div>

        <div class="row">
            <span>Entrega</span>
            <span class="bold">
                {{ $pedido->tipo_entrega === 'delivery' ? 'A domicilio' : 'Recoger en local' }}
            </span>
        </div>

        <div class="row">
            <span>Pago</span>
            <span class="bold">{{ ucfirst($pedido->metodo_pago) }}</span>
        </div>
    </div>

    <div class="section">
        <h3>Cliente</h3>

        <p class="bold">{{ $pedido->cliente->name ?? 'Cliente no disponible' }}</p>

        @if($pedido->direccion)
            <p>Recibe: {{ $pedido->direccion->nombre_recibe ?? 'Sin nombre' }}</p>
            <p>Tel: {{ $pedido->direccion->telefono ?? 'Sin teléfono' }}</p>
        @endif
    </div>

    @if($pedido->tipo_entrega === 'delivery' && $pedido->direccion)
        <div class="section">
            <h3>Dirección</h3>

            <p>
                {{ $pedido->direccion->calle ?? 'Sin calle' }}
                {{ $pedido->direccion->numero ? '#'.$pedido->direccion->numero : '' }},
                {{ $pedido->direccion->colonia ?? 'Sin colonia' }}
            </p>

            @if($pedido->direccion->cruzamientos)
                <p>Cruzamientos: {{ $pedido->direccion->cruzamientos }}</p>
            @endif

            <p>
                {{ $pedido->direccion->ciudad ?? '' }}
                {{ $pedido->direccion->estado ? ', '.$pedido->direccion->estado : '' }}
                {{ $pedido->direccion->codigo_postal ? ' CP '.$pedido->direccion->codigo_postal : '' }}
            </p>

            @if($pedido->direccion->referencia)
                <p>Referencia: {{ $pedido->direccion->referencia }}</p>
            @endif

            @if($pedido->direccion->latitud && $pedido->direccion->longitud)
                <p class="muted">
                    Maps: https://www.google.com/maps?q={{ $pedido->direccion->latitud }},{{ $pedido->direccion->longitud }}
                </p>
            @endif
        </div>
    @endif

    <div class="section">
        <h3>Productos</h3>

        @foreach($pedido->detalles as $detalle)
            <div class="product">
                <p class="bold">{{ $detalle->nombre_producto }}</p>

                @if($detalle->nombre_variante)
                    <p class="muted">{{ $detalle->nombre_variante }}</p>
                @endif

                <div class="row">
                    <span>{{ $detalle->cantidad }} x ${{ number_format($detalle->precio_unitario, 2) }}</span>
                    <span class="bold">${{ number_format($detalle->subtotal, 2) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    @if($pedido->notas_cliente)
        <div class="section">
            <h3>Nota del cliente</h3>
            <p>{{ $pedido->notas_cliente }}</p>
        </div>
    @endif

    <div class="section">
        <div class="row">
            <span>Subtotal</span>
            <span>${{ number_format($pedido->subtotal, 2) }}</span>
        </div>

        <div class="row">
            <span>Envío</span>
            <span>${{ number_format($pedido->costo_envio, 2) }}</span>
        </div>

        @if($pedido->descuento > 0)
            <div class="row">
                <span>Descuento</span>
                <span>-${{ number_format($pedido->descuento, 2) }}</span>
            </div>
        @endif

        <div class="row">
            <span class="bold">Total</span>
            <span class="bold">${{ number_format($pedido->total, 2) }}</span>
        </div>
    </div>

    <button class="btn-print" onclick="window.print()">
        Imprimir ticket
    </button>

</div>

</body>
</html>