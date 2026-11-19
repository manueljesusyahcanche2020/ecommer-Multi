@extends('layouts.dashboard')

@section('title', 'Usuarios')
@section('page-title', 'Usuarios')
@section('page-subtitle', 'Administración de usuarios registrados')

@section('content')

<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Usuarios registrados</h4>

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
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Registro</th>
                    <th class="text-end">Acción</th>
                </tr>
            </thead>

            <tbody>
                @foreach($usuarios as $usuario)
                    <tr>
                        <td>
                            <strong>{{ $usuario->name }}</strong>
                        </td>

                        <td>{{ $usuario->email }}</td>

                        <td>
                            {{ $usuario->role->nombre ?? $usuario->role->name ?? 'Sin rol' }}
                        </td>

                        <td>
                            @if($usuario->activo ?? true)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>

                        <td>
                            {{ $usuario->created_at ? $usuario->created_at->format('d/m/Y') : 'Sin fecha' }}
                        </td>

                        <td class="text-end">
                            <form method="POST"
                                  action="{{ route('admin.usuarios.estado', $usuario->id) }}">
                                @csrf
                                @method('PATCH')

                                <button class="btn btn-sm {{ ($usuario->activo ?? true) ? 'btn-outline-danger' : 'btn-outline-success' }} fw-bold">
                                    {{ ($usuario->activo ?? true) ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

</div>

@endsection