{{-- filepath: c:\Users\Vytenis\Desktop\tfg\restaurante-admin\resources\views\staff\my-shifts.blade.php --}}
@extends('adminlte::page')
@section('title','Mis Turnos')
@section('content')
<div class="container">
  <h1 class="mb-4">Mis Turnos</h1>

  {{-- Card para filtrar por mes --}}
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">
      Filtrar Turnos por Mes
    </div>
    <div class="card-body">
      <form method="GET" action="{{ route('staff.myShifts') }}">
        <div class="row align-items-end">
          <div class="col-md-4">
            <label for="month" class="form-label">Selecciona el Mes:</label>
            <input type="month" name="month" id="month" class="form-control" value="{{ $month }}">
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
              <i class="fas fa-filter me-1"></i> Filtrar
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Card para listar los turnos --}}
  <div class="card shadow-sm">
    <div class="card-header">
      <strong>Turnos Asignados</strong>
    </div>
    <div class="card-body p-0">
      <table class="table table-striped mb-0">
        <thead class="table-dark">
          <tr>
            <th>Fecha Inicio</th>
            <th>Fecha Fin</th>
            <th>Slot</th>
          </tr>
        </thead>
        <tbody>
          @forelse($shifts as $s)
            <tr>
              <td>{{ \Carbon\Carbon::parse($s['fechaHoraInicio'])->format('d/m/Y H:i') }}</td>
              <td>{{ \Carbon\Carbon::parse($s['fechaHoraFin'])->format('d/m/Y H:i') }}</td>
              <td>{{ $s['slotName'] }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted">No tienes turnos asignados.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection