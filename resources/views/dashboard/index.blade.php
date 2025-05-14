@extends('adminlte::page')

@section('title','Dashboard')

@section('content')
<div class="container mt-4">
  <h1>Inicio</h1>

  {{-- FILTROS --}}
  <form method="GET" action="{{ route('dashboard.index') }}" class="row g-3 mb-4">
    @if($restaurantes->count()>1)
      <div class="col-md-2">
        <label class="form-label">Restaurante</label>
        <select name="restauranteId" class="form-select">
          @foreach($restaurantes as $r)
            <option value="{{ $r['id'] }}" {{ $r['id']===$restauranteId?'selected':'' }}>
              {{ $r['nombre'] }}
            </option>
          @endforeach
        </select>
      </div>
    @endif

    <div class="col-md-2">
      <label class="form-label">Ver por</label>
      <select name="filterType" id="filterType" class="form-select" onchange="onFilterTypeChange()">
        <option value="day"   {{ $filterType==='day'   ?'selected':'' }}>Día</option>
        <option value="week"  {{ $filterType==='week'  ?'selected':'' }}>Semana</option>
        <option value="month" {{ $filterType==='month' ?'selected':'' }}>Mes</option>
      </select>
    </div>

    <div class="col-md-2" id="divDate">
      <label class="form-label">Fecha base</label>
      <input type="date" name="baseDate" class="form-control" value="{{ $baseDate }}">
    </div>
    <div class="col-md-2 d-none" id="divMonth">
      <label class="form-label">Mes</label>
      <input type="month" name="baseMonth" class="form-control" value="{{ $baseMonth }}">
    </div>

    <div class="col-md-2">
      <label class="form-label">Estado</label>
      <select name="estado" class="form-select">
        <option value=""           {{ $estado===''           ?'selected':'' }}>Todas</option>
        <option value="Pendiente"  {{ $estado==='Pendiente'  ?'selected':'' }}>Pendiente</option>
        <option value="Confirmada" {{ $estado==='Confirmada' ?'selected':'' }}>Confirmada</option>
        <option value="Cancelada"  {{ $estado==='Cancelada'  ?'selected':'' }}>Cancelada</option>
      </select>
    </div>

    <div class="col-md-2 d-flex align-items-end">
      <button class="btn btn-primary w-100">Aplicar filtros</button>
    </div>
  </form>

  {{-- TOTALES --}}
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="alert alert-info">
        <strong>Reservas hoy ({{ $estado?:'Todas' }}):</strong> {{ $totalHoy }}
      </div>
    </div>
    <div class="col-md-6">
      <div class="alert alert-info">
        <strong>Reservas ({{ $labelDesde }} → {{ $labelHasta }}):</strong> {{ $totalRango }}
      </div>
    </div>
  </div>

  {{-- GRID: Chart + Heatmap --}}
  <div class="row">
    {{-- Chart --}}
    <div class="col-md-6">
      <div class="card mb-4" style="height:400px;">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Reservas</span>
          <button id="toggleType" class="btn btn-sm btn-outline-secondary">Ver como Barras</button>
        </div>
        <div class="card-body position-relative">
          <div id="spinnerChart" class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
          </div>
          <canvas id="reservasChart" class="d-none" style="width:100%; height:100%;"></canvas>
          <div id="noDataChart" class="text-center text-muted py-5 d-none">
            No hay datos para mostrar.
          </div>
        </div>
      </div>
    </div>

    {{-- Heatmap --}}
    {{-- filepath: c:\Users\Vytenis\Desktop\tfg\restaurante-admin\resources\views\dashboard\index.blade.php --}}
    <div class="col-md-6">
        <div class="card mb-4" style="height:400px;">
  <div class="card-header">Calendario de Reservas</div>
  <div class="card-body">
    <div class="heatmap-header">
      @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $dia)
        <div class="heatmap-header-cell">{{ $dia }}</div>
      @endforeach
    </div>
    <div id="calendar" class="heatmap-grid">
      {{-- Celdas en blanco para alinear el primer día --}}
      @for($i = 0; $i < $blankCells; $i++)
        <div class="calendar-cell blank"></div>
      @endfor

      {{-- Renderizar cada día del mes --}}
      @foreach($calendarDays as $day)
        <div class="calendar-cell">
          <div class="calendar-cell-inner" style="background-color: rgba(54,162,235,{{ $day['total'] / $maxCount }});">
            <span class="calendar-cell-label">{{ $day['day'] }}</span>
            @if($day['total'] > 0)
              <span class="calendar-cell-total">{{ $day['total'] }}</span>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
    </div>

  </div>
</div>
@endsection

@section('css')
<style>
  .heatmap-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    font-weight: bold;
    margin-bottom: 4px;
    font-size: 0.9em;
  }
  .heatmap-header-cell { padding: 2px 0; }
/* Cada celda se dimensiona de manera proporcional */
.calendar-cell {
  position: relative;
  padding-top: 100%;
}
.calendar-cell-inner {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: 4px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 2px;
}
.calendar-cell-label {
  font-size: 0.8em;
  font-weight: bold;
  color: #fff;
}
.calendar-cell-total {
  font-size: 0.7em;
  color: #fff;
  align-self: flex-end;
}
  .heatmap-grid {
      display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
  width: 100%;
  }
  .heatmap-cell { padding-top:100%; position:relative; }
  .heatmap-cell-inner {
    position:absolute; inset:0; border-radius:4px;
  }
  .heatmap-cell-label {
    position:absolute; bottom:2px; right:2px;
    font-size:0.7em; color:rgba(0,0,0,0.6);
  }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // refs
  const spinner = document.getElementById('spinnerChart');
  const canvas  = document.getElementById('reservasChart');
  const noData  = document.getElementById('noDataChart');
  const toggle  = document.getElementById('toggleType');
  const ctx     = canvas.getContext('2d');

  // Datos from Blade
  const filterType = "{{ $filterType }}";
  const labelsNorm = @json($labels);
  const datosNorm  = @json($datos);
  const hourly     = @json($hourly);

  // 0) Ocultar spinner y preparar canvas/noData
  spinner.classList.add('d-none');
  noData.classList.add('d-none');
  canvas.classList.remove('d-none');

  // 1) Construcción de etiquetas y datos
  let chartLabels, chartData;
  if (filterType === 'day') {
    chartLabels = Array.from({ length: 24 }, (_, i) => `${i.toString().padStart(2,'0')}:00`);
    chartData   = Array(24).fill(0);
    hourly.forEach(h => {
      if (h.hour >= 0 && h.hour < 24) {
        chartData[h.hour] = h.total;
      }
    });
  } else {
    chartLabels = labelsNorm;
    chartData   = datosNorm;
  }

  // 2) Si todo es cero → mostrar “noData”
  if (chartData.every(v => v === 0)) {
    canvas.classList.add('d-none');
    noData.classList.remove('d-none');
    return;
  }

  // 3) Inicializar Chart.js
  const chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: chartLabels,
      datasets: [{
        label: 'Reservas',
        data: chartData,
        backgroundColor: 'rgba(54,162,235,0.4)',
        borderColor:     'rgba(54,162,235,1)',
        fill: filterType !== 'day'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { title:{ display:true, text: filterType==='day' ? 'Hora' : 'Fecha' } },
        y: {
          beginAtZero:true,
          ticks:{ stepSize:1 },
          title:{ display:true, text:'# Reservas' }
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            title: items => items[0].label + (filterType==='day'?' h':''),
            label: item  => `${item.parsed.y} reserva(s)`
          }
        }
      }
    }
  });

  // 4) Toggle línea ↔ barras
  toggle.addEventListener('click', () => {
    const nuevo = chart.config.type==='line' ? 'bar' : 'line';
    chart.config.type = nuevo;
    chart.config.data.datasets[0].fill = nuevo!=='line';
    toggle.textContent = nuevo==='line'? 'Ver como Barras' : 'Ver como Línea';
    chart.update();
  });

  // 5) Heatmap (igual que antes)
  @php
    $heatmapData = [];
    foreach ($labels as $i => $f) {
      $heatmapData[] = [
        'fecha' => \Carbon\Carbon::createFromFormat('d/m',$f)->format('Y-m-d'),
        'total' => $datos[$i] ?? 0,
      ];
    }
  @endphp
  const heatmapData = @json($heatmapData);
  const maxCount     = heatmapData.reduce((m,x)=>Math.max(m,x.total),1);
  const hm           = document.getElementById('heatmap');

  heatmapData.forEach(item => {
    const cell  = document.createElement('div');
    const inner = document.createElement('div');
    const lbl   = document.createElement('span');

    cell.className  = 'heatmap-cell';
    inner.className = 'heatmap-cell-inner';
    lbl.className   = 'heatmap-cell-label';

    const alpha = item.total===0 ? 0.1 : (item.total/maxCount);
    inner.style.backgroundColor = `rgba(54,162,235,${alpha})`;
    inner.title                 = `${item.fecha}: ${item.total} reserva(s)`;

    const dt = new Date(item.fecha);
    lbl.textContent = String(dt.getDate()).padStart(2,'0') +'/'
                     + String(dt.getMonth()+1).padStart(2,'0');

    cell.appendChild(inner);
    cell.appendChild(lbl);
    hm.appendChild(cell);
  });

});
</script>
@endsection


