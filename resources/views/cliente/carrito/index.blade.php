@extends('layouts.dashboard')

@section('title', 'Mi carrito')
@section('page-title', 'Carrito')
@section('page-subtitle', 'Productos agregados para comprar')

@section('content')

<style>
    .carrito-img { width:64px; height:64px; object-fit:cover; border-radius:12px; border:1px solid #e5e7eb; }
    .carrito-img-empty { width:64px; height:64px; border-radius:12px; background:#f3f4f6; border:1px solid #e5e7eb; display:flex; align-items:center; justify-content:center; color:#9ca3af; }
    .resumen-card { background:#fff7ed; border:1px solid #fed7aa; border-radius:20px; padding:20px; position:sticky; top:90px; }
    .local-carrito-card { border:1px solid #e5e7eb; border-radius:22px; background:white; overflow:hidden; margin-bottom:22px; box-shadow:0 10px 25px rgba(15,23,42,.05); }
    .local-carrito-header { background:#f9fafb; border-bottom:1px solid #e5e7eb; padding:16px 18px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
    .local-badge { background:#fff7ed; color:#9a3412; border:1px solid #fed7aa; border-radius:999px; padding:6px 12px; font-weight:700; font-size:13px; }
    .confirm-box { background:#f9fafb; border-top:1px solid #e5e7eb; padding:16px; }
    .input-qty { width:75px; text-align:center; }
</style>

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Mi carrito</h4>
            <p class="text-muted mb-0">
                Los productos están separados por local para generar pedidos independientes.
            </p>
        </div>

        <a href="{{ route('cliente.catalogo.index') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-store"></i> Seguir comprando
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

    @php
        // $carrito llega del controlador (carritos activos de la BD, con precios tomados de las variantes).
        $carrito = $carrito ?? [];

        $totalGeneral = 0;
        $totalProductos = 0;
        $totalLocales = count($carrito);

        foreach ($carrito as $localCarrito) {
            foreach (($localCarrito['productos'] ?? []) as $item) {
                $totalGeneral += ($item['precio'] ?? 0) * ($item['cantidad'] ?? 1);
                $totalProductos += ($item['cantidad'] ?? 1);
            }
        }
    @endphp

    @if(count($carrito) > 0)

        <div class="row g-4">

            <div class="col-lg-8">

                @foreach($carrito as $localId => $localCarrito)

                    @php
                        $productosLocal = $localCarrito['productos'] ?? [];
                        $subtotalLocal = 0;
                        $cantidadLocal = 0;
                        $problemaStock = false;

                        // Costo de envío hacia la dirección principal (null = no entrega ahí / sin dirección)
                        $costoEnvioLocal = $localCarrito['costo_envio'] ?? null;

                        // Costo de envío hacia cada dirección: [direccion_id => costo|null]
                        $enviosLocal = $localCarrito['envios'] ?? [];

                        foreach ($productosLocal as $item) {
                            $subtotalLocal += ($item['precio'] ?? 0) * ($item['cantidad'] ?? 1);
                            $cantidadLocal += ($item['cantidad'] ?? 1);

                            // stock_disponible null = sin límite
                            if (($item['stock_disponible'] ?? null) !== null
                                && ($item['cantidad'] ?? 1) > $item['stock_disponible']) {
                                $problemaStock = true;
                            }
                        }
                    @endphp

                    <div class="local-carrito-card">

                        <div class="local-carrito-header">
                            <div>
                                <span class="local-badge">
                                    <i class="fa-solid fa-store"></i>
                                    {{ $localCarrito['local_nombre'] ?? 'Local' }}
                                </span>

                                <div class="text-muted small mt-1">
                                    {{ $cantidadLocal }} producto(s) en este pedido
                                </div>

                                <div class="mt-2">
                                    @if(($localCarrito['permite_delivery'] ?? 0))
                                        <span class="badge bg-info text-dark">
                                            <i class="fa-solid fa-motorcycle"></i> Delivery disponible
                                        </span>
                                    @endif

                                    @if(($localCarrito['permite_recoger'] ?? 1))
                                        <span class="badge bg-light text-dark border">
                                            <i class="fa-solid fa-store"></i> Recoger disponible
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-end">
                                <small class="text-muted d-block">Subtotal local</small>
                                <strong>${{ number_format($subtotalLocal, 2) }}</strong>

                                @if(($localCarrito['permite_delivery'] ?? 0))
                                    <div class="mt-1">
                                        @if($costoEnvioLocal === null)
                                            <span class="badge bg-light text-dark border">
                                                <i class="fa-solid fa-motorcycle"></i>
                                                Envío según tu dirección
                                            </span>
                                        @elseif($costoEnvioLocal > 0)
                                            <span class="badge bg-info text-dark">
                                                <i class="fa-solid fa-motorcycle"></i>
                                                Envío: ${{ number_format($costoEnvioLocal, 2) }}
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="fa-solid fa-truck-fast"></i> Envío gratis
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($problemaStock)
                            <div class="alert alert-warning rounded-0 border-0 border-bottom mb-0">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                Algunos productos superan el stock disponible. Ajusta las cantidades para poder confirmar.
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($productosLocal as $itemKey => $item)
                                        @php
                                            $subtotal = ($item['precio'] ?? 0) * ($item['cantidad'] ?? 1);
                                            $stockItem = $item['stock_disponible'] ?? null;
                                            $excedeStock = $stockItem !== null && ($item['cantidad'] ?? 1) > $stockItem;
                                        @endphp

                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    @if(!empty($item['imagen']))
                                                        <img src="{{ asset('storage/' . $item['imagen']) }}"
                                                             alt="{{ $item['nombre'] ?? 'Producto' }}"
                                                             class="carrito-img">
                                                    @else
                                                        <div class="carrito-img-empty">
                                                            <i class="fa-solid fa-image"></i>
                                                        </div>
                                                    @endif

                                                    <div>
                                                        <strong>{{ $item['nombre'] ?? 'Producto' }}</strong>

                                                        {{-- Solo productos con variantes (la variante "Estándar" llega vacía) --}}
                                                        @if(!empty($item['variante_nombre']))
                                                            <br>
                                                            <span class="badge bg-info text-dark mt-1">
                                                                <i class="fa-solid fa-layer-group"></i>
                                                                {{ $item['variante_nombre'] }}
                                                            </span>
                                                        @endif

                                                        @if($excedeStock)
                                                            <br>
                                                            <small class="text-danger">
                                                                {{ $stockItem > 0 ? 'Solo hay ' . $stockItem . ' disponibles' : 'Sin stock' }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="{{ !empty($item['tiene_oferta']) ? 'text-warning fw-bold' : '' }}">
                                                    ${{ number_format($item['precio'] ?? 0, 2) }}
                                                </span>

                                                @if(!empty($item['tiene_oferta']))
                                                    <br>
                                                    <small class="text-muted text-decoration-line-through">
                                                        ${{ number_format($item['precio_normal'] ?? 0, 2) }}
                                                    </small>
                                                @endif
                                            </td>

                                            <td>
                                                <form method="POST"
                                                      action="{{ route('cliente.carrito.actualizar', [$localId, $itemKey]) }}"
                                                      class="d-flex align-items-center gap-1">
                                                    @csrf
                                                    <input type="number"
                                                           name="cantidad"
                                                           value="{{ $item['cantidad'] ?? 1 }}"
                                                           min="1"
                                                           @if($stockItem !== null && $stockItem > 0) max="{{ $stockItem }}" @endif
                                                           class="form-control form-control-sm input-qty {{ $excedeStock ? 'is-invalid' : '' }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Actualizar cantidad">
                                                        <i class="fa-solid fa-rotate"></i>
                                                    </button>
                                                </form>
                                            </td>

                                            <td>
                                                <strong>${{ number_format($subtotal, 2) }}</strong>
                                            </td>

                                            <td class="text-center">
                                                <form method="POST"
                                                      action="{{ route('cliente.carrito.eliminar', [$localId, $itemKey]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Eliminar del carrito"
                                                            onclick="return confirm('¿Deseas quitar este producto del carrito?')">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="confirm-box">
                            <form method="POST" action="{{ route('cliente.carrito.confirmarLocal', $localId) }}">
                                @csrf

                                <div class="row g-3 align-items-end">

                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Tipo de entrega</label>
                                        <select name="tipo_entrega" class="form-select tipo-entrega-select">
                                            @if(($localCarrito['permite_recoger'] ?? 1))
                                                <option value="recoger">Recoger en local</option>
                                            @endif

                                            @if(($localCarrito['permite_delivery'] ?? 0))
                                                <option value="delivery">Entrega a domicilio</option>
                                            @endif
                                        </select>

                                        {{-- El texto y el total se calculan en el JS según la dirección elegida --}}
                                        <small class="text-muted d-block mt-1 envio-info"
                                               data-subtotal="{{ $subtotalLocal }}">
                                            Recoger en local: sin costo de envío.
                                        </small>

                                        <strong class="d-block mt-1 total-con-envio">
                                            Total: ${{ number_format($subtotalLocal, 2) }}
                                        </strong>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Método de pago</label>
                                        <select name="metodo_pago" class="form-select">
                                            <option value="efectivo">Efectivo</option>
                                            <option value="transferencia">Transferencia</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Dirección</label>

                                        <select name="direccion_id" class="form-select direccion-select" disabled>
                                            <option value="" data-envio="">No aplica para recoger</option>

                                            @foreach($direcciones as $direccion)
                                                @php
                                                    // null = este local no entrega en la ciudad de esa dirección
                                                    $envioDireccion = $enviosLocal[$direccion->id] ?? null;
                                                @endphp

                                                <option value="{{ $direccion->id }}"
                                                        data-envio="{{ $envioDireccion === null ? '' : $envioDireccion }}"
                                                        {{ ($direccion->principal && $envioDireccion !== null) ? 'selected' : '' }}>
                                                    {{ $direccion->nombre_recibe }} - {{ $direccion->calle }} {{ $direccion->numero ? '#'.$direccion->numero : '' }}
                                                    @if($envioDireccion === null)
                                                        (fuera de zona)
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>

                                        @if($direcciones->count() == 0)
                                            <small class="text-danger d-block">No tienes direcciones registradas.</small>
                                        @else
                                            <small class="text-muted direccion-ayuda d-block">
                                                Solo se usa para entrega a domicilio.
                                            </small>
                                        @endif
                                    </div>

                                    <div class="col-md-3">
                                        <button type="submit"
                                                class="btn btn-warning text-white fw-bold w-100 btn-confirmar"
                                                data-stock-bloqueado="{{ $problemaStock ? 1 : 0 }}"
                                                {{ $problemaStock ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-check"></i> Confirmar pedido
                                        </button>
                                    </div>

                                    @if($direcciones->count() == 0)
                                        <div class="col-12">
                                            <a href="{{ route('cliente.direcciones.create') }}" class="btn btn-sm btn-outline-danger fw-bold">
                                                <i class="fa-solid fa-location-dot"></i> Crear dirección
                                            </a>
                                        </div>
                                    @endif

                                    <div class="col-12">
                                        <label class="form-label fw-bold">Nota para el vendedor</label>
                                        <textarea name="notas_cliente"
                                                  class="form-control"
                                                  rows="2"
                                                  placeholder="Ej. Sin cebolla, llego en 20 minutos, etc."></textarea>
                                    </div>

                                </div>
                            </form>
                        </div>

                    </div>

                @endforeach

            </div>

            <div class="col-lg-4">
                <div class="resumen-card">
                    <h5 class="fw-bold mb-3">Resumen general</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Locales</span>
                        <strong>{{ $totalLocales }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Productos</span>
                        <strong>{{ $totalProductos }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal general</span>
                        <strong>${{ number_format($totalGeneral, 2) }}</strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">Total sin envío</span>
                        <h4 class="fw-bold mb-0">${{ number_format($totalGeneral, 2) }}</h4>
                    </div>

                    <form method="POST" action="{{ route('cliente.carrito.confirmarTodos') }}">
                        @csrf
                        <input type="hidden" name="tipo_entrega" value="recoger">
                        <input type="hidden" name="metodo_pago" value="efectivo">
                        <input type="hidden" name="direccion_id" value="">

                        <button type="submit" class="btn btn-warning text-white fw-bold w-100">
                            <i class="fa-solid fa-receipt"></i> Confirmar todos
                        </button>
                    </form>

                    <small class="text-muted d-block mt-2">
                        Confirmar todos procesará cada pedido como "Recoger en local". Para entrega a domicilio, confirma cada local individualmente.
                    </small>
                </div>
            </div>

        </div>

    @else

        <div class="text-center py-5">
            <i class="fa-solid fa-cart-shopping fa-3x text-muted mb-3"></i>

            <h5 class="fw-bold">Tu carrito está vacío</h5>

            <p class="text-muted">Agrega productos desde el catálogo.</p>

            <a href="{{ route('cliente.catalogo.index') }}" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-store"></i> Ir al catálogo
            </a>
        </div>

    @endif

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formularios = document.querySelectorAll('.confirm-box form');

        formularios.forEach(function (form) {
            const tipoEntrega = form.querySelector('.tipo-entrega-select');
            const direccion = form.querySelector('.direccion-select');
            const ayuda = form.querySelector('.direccion-ayuda');
            const envioInfo = form.querySelector('.envio-info');
            const totalConEnvio = form.querySelector('.total-con-envio');
            const btnConfirmar = form.querySelector('.btn-confirmar');

            if (!tipoEntrega || !direccion) return;

            const subtotal = Number(envioInfo ? envioInfo.dataset.subtotal : 0) || 0;
            const bloqueadoPorStock = btnConfirmar && btnConfirmar.dataset.stockBloqueado === '1';

            function actualizar() {
                const opcionVacia = direccion.querySelector('option[value=""]');

                let envio = 0;
                let bloqueadoPorEnvio = false;
                let textoEnvio = '';
                let esError = false;

                if (tipoEntrega.value === 'delivery') {
                    direccion.disabled = false;
                    direccion.required = true;

                    if (opcionVacia) {
                        opcionVacia.textContent = 'Selecciona una dirección';
                    }

                    if (ayuda) {
                        ayuda.textContent = 'Obligatoria para entrega a domicilio.';
                        ayuda.classList.remove('text-muted');
                        ayuda.classList.add('text-danger');
                    }

                    const opcion = direccion.options[direccion.selectedIndex];

                    if (!opcion || !opcion.value) {
                        textoEnvio = 'Selecciona una dirección para calcular el envío.';
                        bloqueadoPorEnvio = true;
                    } else if (opcion.dataset.envio === '') {
                        // Esa dirección está en una ciudad donde el local no entrega
                        textoEnvio = 'Este local no entrega en esa dirección.';
                        bloqueadoPorEnvio = true;
                        esError = true;
                    } else {
                        envio = Number(opcion.dataset.envio) || 0;
                        textoEnvio = envio > 0
                            ? 'Envío a domicilio: $' + envio.toFixed(2)
                            : 'Envío a domicilio: gratis';
                    }
                } else {
                    direccion.value = '';
                    direccion.disabled = true;
                    direccion.required = false;

                    if (opcionVacia) {
                        opcionVacia.textContent = 'No aplica para recoger';
                    }

                    if (ayuda) {
                        ayuda.textContent = 'Solo se usa para entrega a domicilio.';
                        ayuda.classList.remove('text-danger');
                        ayuda.classList.add('text-muted');
                    }

                    textoEnvio = 'Recoger en local: sin costo de envío.';
                }

                if (envioInfo) {
                    envioInfo.textContent = textoEnvio;
                    envioInfo.classList.toggle('text-danger', esError);
                    envioInfo.classList.toggle('text-muted', !esError);
                }

                if (totalConEnvio) {
                    totalConEnvio.textContent = 'Total: $' + (subtotal + envio).toFixed(2);
                }

                if (btnConfirmar) {
                    btnConfirmar.disabled = bloqueadoPorStock || bloqueadoPorEnvio;
                }
            }

            tipoEntrega.addEventListener('change', actualizar);
            direccion.addEventListener('change', actualizar);
            actualizar();
        });

        // Evita el doble envío al confirmar pedidos
        const formsConfirmacion = document.querySelectorAll('form[action*="confirmar"]');

        formsConfirmacion.forEach(function (form) {
            form.addEventListener('submit', function () {
                const btn = form.querySelector('button[type="submit"]');

                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';
                }
            });
        });
    });
</script>

@endsection