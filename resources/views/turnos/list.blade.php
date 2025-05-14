@extends('adminlte::page')

@section('title', 'Turnos')

@section('content')
<div class="container">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Turnos</h1>
        <a href="{{ route('turnos.create') }}" class="btn btn-primary">Nuevo turno</a>
    </div>

    @if(empty($turnos))
        <div class="alert alert-warning">
            No hay turnos registrados todavía. Para crear el primer turno, haz clic en <a href="{{ route('turnos.create') }}">Nuevo turno</a>.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Hora inicio</th>
                        <th>Hora fin</th>
                        <th>Aforo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($turnos as $t)
                        <tr>
                            <td>{{ $t['id'] }}</td>
                            <td>{{ $t['nombre'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($t['horaInicio'])->format('H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($t['horaFin'])->format('H:i') }}</td>
                            <td>{{ $t['capacidad'] }}</td>
                            <td class="text-end">
                                <a href="{{ route('turnos.edit', $t['id']) }}" class="btn btn-sm btn-outline-primary">
                                    Editar
                                </a>
                                
                               <form action="{{ route('turnos.destroy', $t['id']) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('¿Eliminar turno?')">
                                        Borrar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
