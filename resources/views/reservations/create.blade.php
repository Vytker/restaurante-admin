{{-- filepath: c:\Users\Vytenis\Desktop\tfg\restaurante-admin\resources\views\reservations\create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Crear Reserva')

@section('content')
<div class="container">
    <h1>Crear Reserva</h1>
    
    @if(session('error'))
       <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
       <div class="alert alert-danger">
         <ul>
             @foreach ($errors->all() as $error)
                 <li>{{ $error }}</li>
             @endforeach
         </ul>
       </div>
    @endif

    <!-- Al cambiar la fecha se recarga la página para consultar los turnos disponibles -->
    <form method="GET" action="{{ route('reservations.create') }}">
         <div class="form-group">
             <label for="fecha">Fecha</label>
             <input type="date" id="fecha" name="fecha" value="{{ $fecha }}" class="form-control" min="{{date('Y-m-d')}}" onchange="this.form.submit()">
         </div>
    </form>
    
    <form method="POST" action="{{ route('reservations.store') }}">
         @csrf
         <input type="hidden" id="fechaReserva" name="fechaReserva" value="{{ $fecha }}"> <!-- Se usará la fecha seleccionada -->
         <div class="form-group">
             <label for="turnoId">Turno</label>
             <select id="turnoId" name="turnoId" class="form-control" required>
                 <option value="">Seleccione turno</option>
                 @foreach($slots as $slot)
                      <option value="{{ $slot['turnoId'] }}" data-hora="{{$slot['hora']}}">

                         {{ $slot['hora'] }} ({{ $slot['plazasDisponibles'] }} plazas disponibles)
                      </option>
                 @endforeach
             </select>
         </div>
         <div class="form-group">
             <label for="nombreCliente">Nombre del Cliente</label>
             <input type="text" id="nombreCliente" name="nombreCliente" class="form-control" required>
         </div>
         <div class="form-group">
             <label for="email">Email</label>
             <input type="email" id="email" name="email" class="form-control" required>
         </div>
         <div class="form-group">
             <label for="numeroComensales">Número de Comensales</label>
             <input type="number" id="numeroComensales" name="numeroComensales" class="form-control" min="1" value="1" required>
         </div>
         <div class="form-group">
             <label for="notas">Notas</label>
             <textarea id="notas" name="notas" class="form-control"></textarea>
         </div>
         <button type="submit" class="btn btn-primary">Crear Reserva</button>
    </form>
</div>

<script>
    // Escuchamos el cambio en el select de turno
    document.getElementById('turnoId').addEventListener('change', function(){
        const select = this;
        const selectedOption = select.options[select.selectedIndex];
        const hora = selectedOption.getAttribute('data-hora'); // Hora del turno seleccionado
        const fecha = document.getElementById('fecha').value;     // Fecha seleccionada
        if(hora && fecha) {
            // Se forma la fechaReserva con formato "YYYY-MM-DDTHH:mm:ss"
            // Si la API requiere segundos, se agregan ":00"
            document.getElementById('fechaReserva').value = fecha + 'T' + hora;
        }
    });
</script>

@endsection