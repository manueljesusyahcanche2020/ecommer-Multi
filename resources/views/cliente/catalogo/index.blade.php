@extends('layouts.dashboard')

@section('title', 'Catálogo')
@section('page-title', 'Catálogo')
@section('page-subtitle', 'Explora locales disponibles')

@section('content')

<style>
    .local-card {
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        overflow: hidden;
        background: white;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .local-cover {
        height: 150px;
        width: 100%;
        object-fit: cover;
        background: #f3f4f6;
    }

    .local-logo {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 18px;
        border: 3px solid white;
        margin-top: -36px;
        background: white;
    }

    .local-body {
        padding: 0 18px 18px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .local-info {
        list-style: none;
        padding: 0;
        margin: 12px 0 0;
        font-size: .85rem;
        color: #4b5563;
    }

    .local-info li {
        display: flex;
        gap: 8px;
        align-items: flex-start;
        margin-bottom: 6px;
    }

    .local-info i {
        width: 16px;
        margin-top: 3px;
        text-align: center;
        color: #f59e0b;
    }

    .local-info a {
        color: inherit;
        text-decoration: none;
    }

    .local-info a:hover {
        text-decoration: underline;
    }

    .horario-details summary {
        cursor: pointer;
        list-style: none;
    }

    .horario-details summary::-webkit-details-marker {
        display: none;
    }

    .horario-lista {
        margin: 6px 0 0;
        padding: 8px 10px;
        background: #f9fafb;
        border-radius: 10px;
        font-size: .8rem;
    }

    .horario-lista div {
        display: flex;
        justify-content: space-between;
        padding: 1px 0;
    }

    .horario-lista .hoy {
        font-weight: 700;
        color: #111827;
    }

    .local-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
</style>

@php
    // Días en el mismo formato que local_horarios.dia (sin acentos)
    $dias = [
        1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves',
        5 => 'viernes', 6 => 'sabado', 0 => 'domingo',
    ];
    $diasNombre = [
        'lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles',
        'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo',
    ];
    $hoyClave = $dias[now()->dayOfWeek];
@endphp

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Locales disponibles</h4>
            <p class="text-muted mb-0">
                Locales encontrados:
                <span class="badge bg-dark">{{ $locales->count() }}</span>
            </p>
        </div>
    </div>

    <form method="GET" action="{{ route('cliente.catalogo.index') }}" class="mb-4">
        <div class="row g-2">
            <div class="col-md-10">
                <input
                    type="text"
                    name="buscar"
                    class="form-control"
                    placeholder="Buscar local, producto, ciudad..."
                    value="{{ request('buscar') }}"
                >
            </div>

            <div class="col-md-2">
                <button class="btn btn-warning text-white fw-bold w-100">
                    <i class="fa-solid fa-search"></i>
                    Buscar
                </button>
            </div>
        </div>
    </form>

    @if($locales->count() > 0)
        <div class="row g-4">
            @foreach($locales as $local)
                @php
                    // Horarios indexados por día
                    $horarios = $local->horarios->keyBy('dia');
                    $horarioHoy = $horarios->get($hoyClave);

                    // WhatsApp: si tiene 10 dígitos se asume México (52)
                    $waNumero = preg_replace('/\D+/', '', (string) $local->whatsapp);
                    if (strlen($waNumero) === 10) {
                        $waNumero = '52' . $waNumero;
                    }
                @endphp

                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="local-card">

                        @if($local->portada)
                            <img src="{{ asset('storage/' . $local->portada) }}" class="local-cover">
                        @else
                            <div class="local-cover d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-store fa-2x text-muted"></i>
                            </div>
                        @endif

                        <div class="local-body">
                            @if($local->logo)
                                <img src="{{ asset('storage/' . $local->logo) }}" class="local-logo">
                            @else
                                <div class="local-logo d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-store text-warning"></i>
                                </div>
                            @endif

                            <h5 class="fw-bold mt-2 mb-1">{{ $local->nombre }}</h5>

                            <p class="text-muted small mb-2">
                                {{ $local->descripcion ?? 'Sin descripción' }}
                            </p>

                            {{-- Estado + tipo de entrega --}}
                            <div class="local-badges">
                                @if($local->estado_local == 'abierto')
                                    <span class="badge bg-success">Abierto</span>
                                @elseif($local->estado_local == 'pausado')
                                    <span class="badge bg-warning text-dark">Pausado</span>
                                @else
                                    <span class="badge bg-secondary">Cerrado</span>
                                @endif

                                @if($local->envio_disponible)
                                    <span class="badge bg-primary">
                                        <i class="fa-solid fa-motorcycle"></i> Envío a domicilio
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border">
                                        <i class="fa-solid fa-ban"></i>
                                        {{ $local->permite_delivery ? 'Sin envío a tu zona' : 'Sin envío a domicilio' }}
                                    </span>
                                @endif

                                @if($local->permite_recoger)
                                    <span class="badge bg-info text-dark">
                                        <i class="fa-solid fa-bag-shopping"></i> Recoger en local
                                    </span>
                                @endif
                            </div>

                            <ul class="local-info">

                                {{-- Costo de envío hacia la dirección principal del cliente --}}
                                @if($local->envio_disponible)
                                    <li>
                                        <i class="fa-solid fa-truck-fast"></i>
                                        <span>
                                            Envío a {{ $direccionPrincipal->ciudad }}:
                                            @if((float) $local->costo_envio_cliente > 0)
                                                <strong>${{ number_format($local->costo_envio_cliente, 2) }}</strong>
                                            @else
                                                <strong class="text-success">Gratis</strong>
                                            @endif
                                        </span>
                                    </li>
                                @endif

                                @if((float) $local->minimo_pedido > 0)
                                    <li>
                                        <i class="fa-solid fa-receipt"></i>
                                        <span>Pedido mínimo: <strong>${{ number_format($local->minimo_pedido, 2) }}</strong></span>
                                    </li>
                                @endif

                                {{-- Dirección --}}
                                @if($local->direccion || $local->ciudad)
                                    <li>
                                        <i class="fa-solid fa-location-dot"></i>
                                        <span>
                                            {{ $local->direccion }}
                                            @if($local->ciudad)
                                                {{ $local->direccion ? '·' : '' }} {{ $local->ciudad }}@if($local->estado), {{ $local->estado }}@endif
                                            @endif
                                        </span>
                                    </li>
                                @endif

                                {{-- Contacto --}}
                                @if($local->telefono)
                                    <li>
                                        <i class="fa-solid fa-phone"></i>
                                        <a href="tel:{{ preg_replace('/\D+/', '', $local->telefono) }}">{{ $local->telefono }}</a>
                                    </li>
                                @endif

                                @if($waNumero)
                                    <li>
                                        <i class="fa-brands fa-whatsapp"></i>
                                        <a href="https://wa.me/{{ $waNumero }}" target="_blank" rel="noopener">
                                            WhatsApp: {{ $local->whatsapp }}
                                        </a>
                                    </li>
                                @endif

                                @if($local->correo)
                                    <li>
                                        <i class="fa-solid fa-envelope"></i>
                                        <a href="mailto:{{ $local->correo }}">{{ $local->correo }}</a>
                                    </li>
                                @endif

                                @if(!$local->telefono && !$waNumero && !$local->correo)
                                    <li>
                                        <i class="fa-solid fa-phone-slash"></i>
                                        <span class="text-muted">Sin datos de contacto</span>
                                    </li>
                                @endif

                                {{-- Horarios --}}
                                <li>
                                    <i class="fa-regular fa-clock"></i>
                                    @if($local->horarios->isEmpty())
                                        <span class="text-muted">Horario no registrado</span>
                                    @else
                                        <details class="horario-details flex-grow-1">
                                            <summary>
                                                @if(!$horarioHoy || $horarioHoy->cerrado || !$horarioHoy->hora_apertura || !$horarioHoy->hora_cierre)
                                                    Hoy: <strong class="text-danger">cerrado</strong>
                                                @else
                                                    Hoy:
                                                    <strong>
                                                        {{ substr($horarioHoy->hora_apertura, 0, 5) }}
                                                        - {{ substr($horarioHoy->hora_cierre, 0, 5) }}
                                                    </strong>
                                                @endif
                                                <small class="text-muted">· ver semana</small>
                                            </summary>

                                            <div class="horario-lista">
                                                @foreach($diasNombre as $clave => $nombreDia)
                                                    @php $h = $horarios->get($clave); @endphp
                                                    <div class="{{ $clave === $hoyClave ? 'hoy' : '' }}">
                                                        <span>{{ $nombreDia }}</span>
                                                        <span>
                                                            @if(!$h || $h->cerrado || !$h->hora_apertura || !$h->hora_cierre)
                                                                Cerrado
                                                            @else
                                                                {{ substr($h->hora_apertura, 0, 5) }} - {{ substr($h->hora_cierre, 0, 5) }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    @endif
                                </li>
                            </ul>

                            <div class="mt-auto pt-3">
                                <a href="{{ route('cliente.catalogo.local', $local->id) }}" class="btn btn-warning text-white fw-bold w-100">
                                    Ver local
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <i class="fa-solid fa-shop fa-3x text-muted mb-3"></i>
            <h5 class="fw-bold">No hay locales disponibles</h5>
            <p class="text-muted">Intenta buscar con otra palabra.</p>
        </div>
    @endif

</div>

@endsection