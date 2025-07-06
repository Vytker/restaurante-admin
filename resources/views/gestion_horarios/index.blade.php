
@extends('adminlte::page')

@section('css')
  <!-- FullCalendar Core CSS desde unpkg -->
  <link
    href="{{ asset('vendor/fullcalendar/css/main.min.css') }}"
    rel="stylesheet"
  />

  <style>
/* Estilos personalizados para los eventos */
.fc-daygrid-block-event {
  display: flex;
  align-items: center;
  justify-content: center;
}
/* Permitir que el texto del título del evento se ajuste en varias líneas si es largo */
.fc-daygrid-block-event .fc-event-title {
  white-space: normal;
}
  .fc-event, /* Aplica a todos los eventos */
  .fc-daygrid-event-dot { /* También a los indicadores si se usan */
    cursor: pointer;
  }

    </style>
@endsection

@section('content')
<div class="container">
  <h1>Gestión de Horarios</h1>

  {{-- Selector de día clásico --}}
 

  {{-- Calendario --}}
  <div id="calendar"></div>
</div>

{{-- Modal de Asignación --}}
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="assignForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          Asignar horario para <span id="modalDate"></span>
        </h5>
        <button type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Cerrar">
               <span aria-hidden="true">&times;</span>
              </button>
      </div>
      <div class="modal-body">
        {{-- Fecha --}}
        <input type="hidden" name="date" id="inputDate" />

        {{-- Listado de asignaciones existentes --}}
        <div id="assignedSection" class="mb-4">
          <h6>Asignaciones para <span id="modalDate"></span></h6>
          <div id="assignedList">
            <!-- Aquí se inyectará el HTML con los turnos y empleados -->
          </div>
          <hr>
        </div>
         {{-- 2) CONFIRM DELETE (oculto por defecto) --}}
        <div id="confirmDeleteSection" class="d-none text-center">
          <p>¿Seguro que quieres eliminar esta asignación?</p>
          <button type="button" id="confirmDeleteBtn" class="btn btn-danger me-2">
            Sí, eliminar
          </button>
          <button type="button" id="cancelDeleteBtn" class="btn btn-secondary">
            No, volver
          </button>
        </div>

        <hr>

        {{-- Slot --}}
        <div class="mb-3">
          <label for="slotSelect" class="form-label">Turno</label>
          <select name="slotId"
                  id="slotSelect"
                  class="form-control"
                  required>
            @if(count($slots))
              @foreach($slots as $slot)
                <option value="{{ $slot['id'] }}">
                  {{ $slot['name'] }}
                  ({{ \Carbon\Carbon::parse($slot['start'])->format('H:i') }}
                   – {{ \Carbon\Carbon::parse($slot['end'])->format('H:i') }})
                </option>
              @endforeach
            @else
              <option disabled>Sin turnos definidos</option>
            @endif
          </select>
        </div>

        {{-- Empleado --}}
        <div class="mb-3">
          <label for="empleadoId" class="form-label">Empleado</label>
          <select name="empleadoId"
                  id="empleadoId"
                  class="form-control"
                  required>
            @foreach($empleados as $emp)
              <option value="{{ $emp['id'] }}"
                {{ old('empleadoId') == $emp['id'] ? 'selected' : '' }}>
                {{ $emp['name'] ?? $emp['userName'] ?? 'Empleado '.$emp['id'] }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button"
                class="btn btn-secondary"
                data-dismiss="modal">
          Cancelar
        </button>
        <button type="submit" class="btn btn-primary">Asignar</button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('js')
<script>
  window.JwtToken     = "{{ Session::get('jwt') }}";
    window.RestaurantId = "{{ Session::get('restaurante_id') }}";
    window.ApiBase      = "{{ config('services.horarios.url') }}";
</script>
  <!-- Variables globales pasadas desde PHP -->
  <script>
    window.SLOTS     = @json($slots);
    window.EMPLEADOS = @json($empleados);
  </script>

  <!-- FullCalendar bundle global -->
  <script src="{{ asset('vendor/fullcalendar/js/index.global.min.js') }}"></script>


  <script>
    document.addEventListener('DOMContentLoaded', function() {
      console.log('🚀 DOM listo, inicializando calendario…');
      const restId    = window.RestaurantId;

      const token          = '{{ Session::get("jwt") }}';
      const apiBase        = '{{ config("services.horarios.url") }}';
      const assignmentsUrl = '{{ route("horarios.assignments") }}';
      const assignUrl = `${apiBase}/assignments` ;

      const calendarEl     = document.getElementById('calendar');
      const modalEl        = document.getElementById('assignModal');
      const bsModal        = new bootstrap.Modal(modalEl);
      const inputDate      = document.getElementById('inputDate');
      const modalDate      = document.getElementById('modalDate');
      const slotSelect     = document.getElementById('slotSelect');
      const form           = document.getElementById('assignForm');
      const assignedList   = document.getElementById('assignedList');

      // Inicializamos FullCalendar
      const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        firstDay:1,
        buttonText: {
          today: 'Hoy',
          month: 'Mes',
          week:  'Semana',
          day:   'Día',
          list:  'Lista'
        },
        initialView: 'dayGridMonth',
        headerToolbar: {
          left:   'prev,next today',
          center: 'title',
          right:  'dayGridMonth,timeGridWeek,timeGridDay'
        },

        // ------------ Renderizado de eventos como bloques de día completo ------------
        dayMaxEventRows: 5,       // hasta 3 bloques, luego "+n more"
        eventDisplay:    'block', // fuerza el modo bloque

        // Llamada a tu API para traer asignaciones
       events: async function(fetchInfo, success, failure) {
  try {
    // 1) Url al nuevo endpoint de rango
    const url = new URL(`${apiBase}/assignments/range`);
    url.searchParams.set('start', fetchInfo.startStr);
    url.searchParams.set('end',   fetchInfo.endStr);

    // 2) Fetch con Bearer token
    const resp = await fetch(url, {
      headers: { 'Authorization': 'Bearer ' + token,
      'X-Restaurante-Id': restId

      }
      
    });
    if (!resp.ok) throw new Error('HTTP ' + resp.status);
    const data = await resp.json();

    // 3) Mapear cada AssignmentDto a un evento FullCalendar
    const fcEvents = data.map(item => {
      const emp  = EMPLEADOS.find(e => e.id === item.empleadoId) || {};
      const slot = SLOTS    .find(s => s.id === item.slotId)    || {};
      const name = emp.fullName ?? emp.name ?? emp.username ?? `Empleado ${item.empleadoId}`;
      const turn = slot.name    ?? 'Turno desconocido';

      return {
        id:     item.id,
        title:  `${name} (${turn})`,
        start:  item.fechaHoraInicio,
        allDay: true,
        color:  '#3788d8'
      };
    });

    // 4) Entregar a FullCalendar
    success(fcEvents);

  } catch(err) {
    console.error('Error cargando asignaciones por rango:', err);
    failure(err);
  }
},

       
        // Al hacer click en una fecha, abrimos el modal y cargamos las asignaciones
        dateClick: async function(info) {
          inputDate.value       = info.dateStr;
          modalDate.textContent = info.dateStr;
          assignedList.innerHTML = '<p>Cargando asignaciones…</p>';

          try {
            const url = new URL(assignmentsUrl, window.location.origin);
            url.searchParams.set('day', info.dateStr);
            const resp = await fetch(url, {
              headers: { 'Authorization': 'Bearer ' + token,
                'X-Restaurante-Id': restId
               }
              
            });
            const data = await resp.json();
            console.log('💥 data de eventos:', data);
            // Agrupamos por slot y generamos el listado HTML
            const bySlot = {};
            data.forEach(a => {
              (bySlot[a.slotId] = bySlot[a.slotId] || []).push(a.empleadoId);
            });

            let html = '';
            SLOTS.forEach(slot => {
              html += '<strong>' + slot.name +
                      ' (' + slot.start.slice(0,5) + '–' + slot.end.slice(0,5) + ')</strong><br>';
              const assigned = bySlot[slot.id] || [];
              if (assigned.length) {
                html += '<ul>';
                assigned.forEach(empId => {
                  const emp = EMPLEADOS.find(e => e.id == empId);
                  const name = emp?.fullName || emp?.name || emp?.username || `Empleado ${empId}`;
                  html += '<li>' + name + '</li>';
                });
                html += '</ul>';
              } else {
                html += '<em>Sin empleados asignados</em><br>';
              }
            });
            assignedList.innerHTML = html;
          } catch (err) {
            console.error('Error cargando asignaciones en modal:', err);
            assignedList.innerHTML = '<p class="text-danger">Error cargando asignaciones.</p>';
          }

          slotSelect.selectedIndex = 0;
          bsModal.show();
        },
         eventClick: function(info) {
    // info.event.id es el ID de la asignación
    const asignId = info.event.id;
    const empTurn = info.event.title;  // "Juan Pérez (Tarde)"

    // preguntamos si de verdad queremos eliminar
    if (!confirm(`¿Eliminar asignación?\n${empTurn}`)) {
      return;
    }

    // llamamos al DELETE
    fetch(`${apiBase}/assignments/${asignId}`, {
      method: 'DELETE',
      headers: {
        'Authorization': 'Bearer ' + token,
        'X-Restaurante-Id': restId
      }
    })
    .then(resp => {
      if (!resp.ok) throw new Error('HTTP ' + resp.status);
      // refrescamos el calendario
      calendar.refetchEvents();
      alert('Asignación eliminada.');
    })
    .catch(err => {
      console.error('Error eliminando asignación:', err);
      alert('No se pudo eliminar. Revisa la consola.');
    });
  }
});

      calendar.render();

      // Envío del formulario de asignación
      form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const payload = {
          date:       inputDate.value,
          slotId:     slotSelect.value,
          empleadoId: document.getElementById('empleadoId').value
        };
        try {
          const resp = await fetch(assignUrl, {
            method:  'POST',
            headers: {
              'Authorization': 'Bearer ' + token,
              'X-Restaurante-Id': restId,
              'Content-Type':  'application/json',
              
            },
            body: JSON.stringify(payload)
          });
          if (!resp.ok) throw new Error('HTTP ' + resp.status);
          bsModal.hide();
          calendar.refetchEvents();
        } catch (err) {
          console.error('Error al asignar turno:', err);
          alert('Error al asignar, revisa la consola.');
        }
      });
    });
  </script>
@endsection
