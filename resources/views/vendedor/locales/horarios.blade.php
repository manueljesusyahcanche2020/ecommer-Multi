@extends('layouts.dashboard')

@section('title', 'Horarios del local')
@section('page-title', 'Horarios')
@section('page-subtitle', 'Modifica los horarios de atención del local')

@section('content')

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Horarios de {{ $local->nombre }}</h4>
            <p class="text-muted mb-0">Edita los días y horas de atención.</p>
        </div>

        <a href="{{ route('vendedor.locales.show', $local->id) }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-arrow-left"></i>
            Volver al local
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i>
            {{ session('success') }}

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Revisa los datos:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $dias = [
            'lunes' => 'Lunes',
            'martes' => 'Martes',
            'miercoles' => 'Miércoles',
            'jueves' => 'Jueves',
            'viernes' => 'Viernes',
            'sabado' => 'Sábado',
            'domingo' => 'Domingo',
        ];

        $horariosActuales = $local->horarios->keyBy('dia');
    @endphp

    <form method="POST" action="{{ route('vendedor.locales.horarios.update', $local->id) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">

            @foreach($dias as $diaKey => $diaNombre)
                @php
                    $horario = $horariosActuales->get($diaKey);

                    $horaApertura = old(
                        'horarios.'.$diaKey.'.hora_apertura',
                        $horario?->hora_apertura ? substr($horario->hora_apertura, 0, 5) : '08:00'
                    );

                    $horaCierre = old(
                        'horarios.'.$diaKey.'.hora_cierre',
                        $horario?->hora_cierre ? substr($horario->hora_cierre, 0, 5) : '20:00'
                    );

                    $cerrado = old(
                        'horarios.'.$diaKey.'.cerrado',
                        $horario?->cerrado
                    );
                @endphp

                <div class="col-12">
                    <div class="border rounded-4 p-3">
                        <div class="row g-3 align-items-center">

                            <div class="col-md-3">
                                <strong>{{ $diaNombre }}</strong>
                                <input type="hidden" name="horarios[{{ $diaKey }}][dia]" value="{{ $diaKey }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Hora de apertura</label>
                                <input type="time"
                                       name="horarios[{{ $diaKey }}][hora_apertura]"
                                       class="form-control"
                                       value="{{ $horaApertura }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Hora de cierre</label>
                                <input type="time"
                                       name="horarios[{{ $diaKey }}][hora_cierre]"
                                       class="form-control"
                                       value="{{ $horaCierre }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold d-block">Estado</label>

                                <label class="form-check">
                                    <input type="checkbox"
                                           name="horarios[{{ $diaKey }}][cerrado]"
                                           value="1"
                                           class="form-check-input"
                                           {{ $cerrado ? 'checked' : '' }}>

                                    <span class="form-check-label">
                                        Cerrado este día
                                    </span>
                                </label>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach

        </div>

        <div class="d-flex gap-2 mt-4">
            <a href="{{ route('vendedor.locales.show', $local->id) }}" class="btn btn-outline-secondary fw-bold">
                Cancelar
            </a>

            <button type="submit" class="btn btn-warning text-white fw-bold">
                <i class="fa-solid fa-floppy-disk"></i>
                Guardar horarios
            </button>
        </div>

    </form>

</div>

@endsection