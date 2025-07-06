@extends('adminlte::page')

@section('title', 'Gestión de Slots')

@section('content')
<div class="container mt-4">
  <h1>Gestión de Slots</h1>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('slots.create') }}" class="btn btn-primary">Nuevo Slot</a>
  </div>

  @if(empty($slots) || count($slots) === 0)
    <div class="alert alert-warning">
      No hay slots registrados.
    </div>
  @else
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Hora Inicio</th>
            <th>Hora Fin</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach($slots as $slot)
            <tr>
              <td>{{ $slot['id'] }}</td>
              <td>{{ $slot['name'] }}</td>
              <td>{{ \Carbon\Carbon::parse($slot['start'])->format('H:i') }}</td>
              <td>{{ \Carbon\Carbon::parse($slot['end'])->format('H:i') }}</td>
              <td class="text-end">
                <a href="{{ route('slots.edit', $slot['id']) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                <form action="{{ route('slots.destroy', $slot['id']) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('¿Eliminar este slot?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger">Borrar</button>
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