@extends('adminlte::page')

@section('title', isset($turno) ? 'Editar turno' : 'Nuevo turno')

@section('content')
<div class="container">
    <h1 class="h3 mb-4">{{ isset($turno) ? 'Editar turno' : 'Nuevo turno' }}</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ isset($turno)
                     ? route('turnos.update', $turno['id'])
                     : route('turnos.store') }}">
        @csrf

        @if(isset($turno))
            @method('PUT')
        @endif

        <div class="mb-3">
            <label class="form-label" for="nombre">Nombre</label>
            <input id="nombre" name="nombre" type="text" class="form-control"
                   value="{{ old('nombre', $turno['nombre'] ?? '') }}" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="horaInicio">Hora inicio</label>
                <input id="horaInicio" name="horaInicio" type="time" class="form-control"
                       value="{{ old('horaInicio', isset($turno) ? \Carbon\Carbon::parse($turno['horaInicio'])->format('H:i') : '') }}"
                       required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label" for="horaFin">Hora fin</label>
                <input id="horaFin" name="horaFin" type="time" class="form-control"
                       value="{{ old('horaFin', isset($turno) ? \Carbon\Carbon::parse($turno['horaFin'])->format('H:i') : '') }}"
                       required>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label" for="Capacidad">Capacidad</label>
            <input id="capacidad" name="capacidad" type="number" min="1" class="form-control"
                   value="{{ old('capacidad', $turno['capacidad'] ?? 1) }}" required>
        </div>

        <button class="btn btn-primary">
            {{ isset($turno) ? 'Actualizar' : 'Crear' }}
        </button>
        <a href="{{ route('turnos.list') }}" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
</div>
@endsection
