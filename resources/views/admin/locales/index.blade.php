@extends('layouts.dashboard')

@section('title', 'Locales')
@section('page-title', 'Locales')
@section('page-subtitle', 'Administración de locales registrados')

@section('content')

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Locales registrados</h4>

        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary fw-bold">
            <i class="fa-solid fa-arrow-left"></i>
            Volver
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Local</th>
                    <th>Vendedor</th>
                    <th>Ciudad</th>
                    <th>Estado local</th>
                    <th>Activo</th>
                    <th>Registro</th>
                    <th class="text-end">Acción</th>
                </tr>
            </thead>

            <tbody>
                @forelse($locales as $local)
                    <tr>
                        <td>
                            <strong>{{ $local->nombre }}</strong>
                        </td>

                        <td>
                            {{ $local->vendedor->name ?? 'Sin vendedor' }}
                        </td>

                        <td>
                            {{ $local->ciudad ?? 'Sin ciudad' }}
                        </td>

                        <td>
                            @if($local->estado_local === 'abierto')
                                <span class="badge bg-success">Abierto</span>
                            @elseif($local->estado_local === 'pausado')
                                <span class="badge bg-warning text-dark">Pausado</span>
                            @else
                                <span class="badge bg-secondary">Cerrado</span>
                            @endif
                        </td>

                        <td>
                            @if($local->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>

                        <td>
                            {{ $local->created_at ? $local->created_at->format('d/m/Y') : 'Sin fecha' }}
                        </td>

                        <td class="text-end">
                            <form method="POST"
                                  action="{{ route('admin.locales.estado', $local->id) }}"
                                  onsubmit="return confirm('¿Seguro que quieres cambiar el estado de este local?');">
                                @csrf
                                @method('PATCH')

                                <button class="btn btn-sm {{ $local->activo ? 'btn-outline-danger' : 'btn-outline-success' }} fw-bold">
                                    {{ $local->activo ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            No hay locales registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

</div>

@endsection