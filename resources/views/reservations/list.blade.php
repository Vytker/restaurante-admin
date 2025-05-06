{{-- filepath: c:\Users\Vytenis\Desktop\tfg\restaurante-admin\resources\views\reservations\list.blade.php --}}
@extends('adminlte::page')

@section('title', 'Reservas del Restaurante')

@section('content')
<div class="container mt-4">
    
    <h1>Reservas del Restaurante</h1>
    @include('reservations._filter')
    
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
              @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
              @endforeach
            </ul>
        </div>
    @endif

    @if(count($reservas) > 0)
    <table class="table table-bordered">
        <thead>
            <tr>
                
                <th>Cliente</th>
                <th>Email</th>
                <th>Fecha Reserva</th>
                <th>N° Comensales</th>
                <th>Código</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservas as $reserva)
            <tr>
                
                <td>{{ $reserva['nombreCliente'] }}</td>
                <td>{{ $reserva['email'] }}</td>
                <td>{{ $reserva['fechaReserva'] }}</td>
                <td>{{ $reserva['numeroComensales'] }}</td>
                <td>{{ $reserva['codigo'] }}</td>
                <td>
                    {{
                      $reserva['estado'] == '0' ? 'Pendiente' : (
                        $reserva['estado'] == '1' ? 'Confirmada' : (
                          $reserva['estado'] == '2' ? 'Cancelada' : 'Rechazada'
                        )
                      )
                    }}
                  </td>
                <td>
                    @if($reserva['estado'] == '0')
                        <form class="actionForm" action="{{ route('reservations.updateStatus', $reserva['id']) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="estado" value="1">
                            <button type="button" class="btn btn-success btn-sm confirm-action">Confirmar</button>
                        </form>
                        <form class="actionForm" action="{{ route('reservations.updateStatus', $reserva['id']) }}" method="POST" style="display:inline; margin-left:5px;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="estado" value="3">
                            <button type="button" class="btn btn-warning btn-sm confirm-action">Rechazar</button>
                        </form>
                    @elseif($reserva['estado'] == '1')
                        <form class="actionForm" action="{{ route('reservations.updateStatus', $reserva['id']) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="estado" value="2">
                            <button type="button" class="btn btn-danger btn-sm confirm-action">Cancelar</button>
                        </form>
                    @elseif($reserva['estado'] == '3')
                        <form class="actionForm" action="{{ route('reservations.updateStatus', $reserva['id']) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="estado" value="1">
                            <button type="button" class="btn btn-success btn-sm confirm-action">Confirmar</button>
                        </form>
                    @else
                        <span class="text-muted">Sin acción</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p>No hay reservas registradas.</p>
    @endif

    <!-- Modal de confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form id="confirmForm" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="estado" id="confirmEstado" value="">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmModalLabel">Confirmar acción</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            ¿Está seguro de realizar esta acción?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Confirmar</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
  <script>
      // Usando jQuery (incluido con AdminLTE) para manejar el modal de confirmación
      $(".confirm-action").on("click", function(){
          var $form = $(this).closest(".actionForm");
          var actionUrl = $form.attr("action");
          // Configuramos el form del modal con la acción y el estado que queremos enviar
          $("#confirmForm").attr("action", actionUrl);
          var estadoValue = $form.find("input[name='estado']").val();
          $("#confirmEstado").val(estadoValue);
          // Mostramos el modal
          $("#confirmModal").modal("show");
      });
  </script>
@endsection