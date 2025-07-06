@extends('adminlte::page')

@section('title','Calendario de Turnos')

@section('css')
  <!-- FullCalendar CSS -->
  <link href="{{ asset('vendor/fullcalendar/css/main.min.css') }}" rel="stylesheet"/>
@endsection

@section('content')
<div class="container">
  <h1 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>Turnos</h1>
  <div id="calendar"></div>
</div>

<div class="modal fade" id="dayInfoModal" tabindex="-1" aria-labelledby="dayInfoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dayInfoModalLabel">Información del día: <span id="modalDate"></span></h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p id="dayInfoContent">Aquí puedes cargar la información completa de los turnos asignados a este día.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('js')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- FullCalendar JS -->
<script src="{{ asset('vendor/fullcalendar/js/index.global.min.js') }}"></script>
<script>
  // Variables proporcionadas desde PHP
  const JWT_TOKEN     = "{{ session('jwt') }}";
  const RESTAURANT_ID = "{{ session('restaurante_id') }}";
  const API_BASE      = "{{ config('services.horarios.url') }}";
  const SLOTS         = @json($slots);
  const EMPLOYEES     = @json($employees);

  /**
   * Devuelve la franja horaria en formato "HH:MM‑HH:MM".
   * Acepta varias posibles convenciones de nombres que pueda traer la API.
   */
  const formatSlotHours = (slot) => {
    if (!slot) return '';
    const rawStart = slot.startTime ?? slot.start ?? slot.Start ?? slot.horaInicio ?? null;
    const rawEnd   = slot.endTime   ?? slot.end   ?? slot.End   ?? slot.horaFin    ?? null;
    if (!rawStart || !rawEnd) return '';
    const start = String(rawStart).substring(0,5);
    const end   = String(rawEnd).substring(0,5);
    return `${start}-${end}`;
  };

  document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
      locale: 'es',
      height: 'auto',
      firstDay : 1,            // Lunes como primer día de la semana
      timeZone: 'UTC',         // Fechas manejadas en UTC
      initialView: 'dayGridMonth',
      headerToolbar: {
        left:   'prev,next today',
        center: 'title',
        right:  ''
      },

      // ────────────────────── Eventos ──────────────────────
      events: async (fetchInfo, success, failure) => {
        try {
          const url = new URL(`${API_BASE}/assignments/range`);
          url.searchParams.set('start', fetchInfo.startStr);
          url.searchParams.set('end',   fetchInfo.endStr);

          const resp = await fetch(url, {
            headers: {
              'Authorization': `Bearer ${JWT_TOKEN}`,
              'X-Restaurante-Id': RESTAURANT_ID
            }
          });
          if (!resp.ok) throw new Error('HTTP ' + resp.status);

          const assignments = await resp.json(); // [{ id, slotId, empleadoId, fechaHoraInicio, ... }]

          const events = assignments.map(a => {
            const slot = SLOTS.find(s => s.id === a.slotId)         || {};
            const emp  = EMPLOYEES.find(e => e.id === a.empleadoId) || {};

            const slotHours = formatSlotHours(slot);
            const title = `${emp.fullName || 'ID:' + a.empleadoId} — ${slot.name || 'Turno'}${slotHours ? ' (' + slotHours + ')' : ''}`;

            return {
              id:    a.id,
              title,
              start: a.fechaHoraInicio,
              allDay: true
            };
          });

          success(events);
        } catch (err) {
          console.error('Error cargando turnos:', err);
          failure(err);
        }
      },

      // ─────────────────── Click en día (modal) ────────────────────
      dateClick: (info) => {
        document.getElementById('modalDate').textContent = info.dateStr;
        document.getElementById('dayInfoContent').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando turnos...';

        const url = new URL(`${API_BASE}/assignments`);
        url.searchParams.set('date', info.dateStr);

        fetch(url, {
          headers: {
            'Authorization': `Bearer ${JWT_TOKEN}`,
            'X-Restaurante-Id': RESTAURANT_ID
          }
        })
          .then(resp => {
            if (!resp.ok) throw new Error(resp.status);
            return resp.json();
          })
          .then(data => {
            const grouped = {};
            data.forEach(item => {
              const slot = SLOTS.find(s => s.id === item.slotId) || {};
              const emp  = EMPLOYEES.find(e => e.id === item.empleadoId) || {};

              const slotHours = formatSlotHours(slot);
              const slotLabel = `${slot.name || 'Turno'}${slotHours ? ' (' + slotHours + ')' : ''}`;
              const empName   = emp.fullName || ('ID:' + item.empleadoId);

              (grouped[slotLabel] ||= []).push(empName);
            });

            if (Object.keys(grouped).length === 0) {
              document.getElementById('dayInfoContent').innerHTML = '<p class="text-muted">No hay turnos asignados para este día.</p>';
              return;
            }

            let html = '';
            for (const [label, names] of Object.entries(grouped)) {
              html += `<h5>${label}</h5><ul>`;
              names.forEach(n => html += `<li>${n}</li>`);
              html += '</ul>';
            }
            document.getElementById('dayInfoContent').innerHTML = html;
          })
          .catch(err => {
            console.error('Error fetching day info:', err);
            document.getElementById('dayInfoContent').innerHTML = '<p class="text-danger">Error al cargar los turnos de este día.</p>';
          });

        const myModal = new bootstrap.Modal(document.getElementById('dayInfoModal'));
        myModal.show();
      },

      eventClick: () => {}
    });

    calendar.render();
  });
</script>
@endsection
