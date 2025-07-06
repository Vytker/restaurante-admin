{{-- resources/views/slots/form.blade.php --}}
@extends('adminlte::page')

@section('title', isset($slot) ? 'Editar Slot' : 'Nuevo Slot')

@section('content')
<div class="container mt-4">
  <h1>{{ isset($slot) ? 'Editar Slot' : 'Nuevo Slot' }}</h1>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST"
        action="{{ isset($slot)
                    ? route('slots.update', $slot['id'])
                    : route('slots.store') }}">
    @csrf
    @if(isset($slot)) @method('PUT') @endif

    <div class="mb-3">
      <label for="name" class="form-label">Nombre</label>
      <input type="text" name="name" id="name" class="form-control"
             value="{{ old('name', $slot['name'] ?? '') }}"
             required>
    </div>

    @php
      // Si vienen con segundos, los quitamos aquí:
      $startVal = isset($slot['start'])
        ? \Carbon\Carbon::createFromFormat('H:i:s', $slot['start'])
                        ->format('H:i')
        : null;
      $endVal   = isset($slot['end'])
        ? \Carbon\Carbon::createFromFormat('H:i:s', $slot['end'])
                        ->format('H:i')
        : null;
    @endphp

    <div class="mb-3">
      <label for="start" class="form-label">Hora de Inicio</label>
      <input type="time" name="start" id="start" class="form-control"
             value="{{ old('start', $startVal) }}"
             required>
    </div>

    <div class="mb-3">
      <label for="end" class="form-label">Hora de Fin</label>
      <input type="time" name="end" id="end" class="form-control"
             value="{{ old('end', $endVal) }}"
             required>
    </div>

    <div class="d-flex justify-content-between">
      <button type="submit"
              class="btn btn-success">
        {{ isset($slot) ? 'Actualizar Slot' : 'Crear Slot' }}
      </button>
      <a href="{{ route('slots.index') }}"
         class="btn btn-secondary">Volver</a>
    </div>
  </form>
</div>
@endsection
