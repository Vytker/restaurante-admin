{{-- resources/views/gestion_horarios/form.blade.php --}}
@extends('adminlte::page')

@section('content')
<div class="container">
  <h1>Nuevo Turno (Slot)</h1>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('horarios.createSlot') }}">
    @csrf

    {{-- Nombre del turno --}}
    <div class="mb-3">
      <label for="name" class="form-label">Nombre</label>
      <input
        type="text"
        id="name"
        name="name"
        class="form-control"
        value="{{ old('name') }}"
        required
      >
    </div>

    {{-- Hora de inicio --}}
    <div class="mb-3">
      <label for="start" class="form-label">Hora Inicio</label>
      <input
        type="time"
        id="start"
        name="start"
        class="form-control"
        value="{{ old('start') }}"
        required
      >
    </div>

    {{-- Hora de fin --}}
    <div class="mb-3">
      <label for="end" class="form-label">Hora Fin</label>
      <input
        type="time"
        id="end"
        name="end"
        class="form-control"
        value="{{ old('end') }}"
        required
      >
    </div>

    <button type="submit" class="btn btn-success">Crear Turno</button>
    <a href="{{ route('horarios.list') }}" class="btn btn-secondary">Volver</a>
  </form>
</div>
@endsection
