{{-- filepath: c:\Users\Vytenis\Desktop\tfg\restaurante-admin\resources\views\reservations\filter.blade.php --}}
@extends('adminlte::page')

@section('title', 'Filtrar Reservas')

@section('content')
<div class="container mt-4">
    <h1>Filtrar Reservas</h1>
    <form method="GET" action="{{ route('reservations.filter') }}">
        <div class="form-row">
            <!-- Filtro por Estado -->
            <div class="form-group col-md-3">
                <label for="estado">Estado:</label>
                <select name="estado" id="estado" class="form-control">
                    <option value="">Todos</option>
                    <option value="Pendiente" {{ request('estado') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="Confirmada" {{ request('estado') == 'Confirmada' ? 'selected' : '' }}>Confirmada</option>
                    <option value="Cancelada" {{ request('estado') == 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                    <option value="Rechazada" {{ request('estado') == 'Rechazada' ? 'selected' : '' }}>Rechazada</option>
                </select>
            </div>
            <!-- Filtro por Fecha (desde) -->
            <div class="form-group col-md-3">
                <label for="fechaDesde">Fecha desde:</label>
                <input type="date" name="fechaDesde" id="fechaDesde" class="form-control" value="{{ request('fechaDesde') }}">
            </div>
            <!-- Filtro por Fecha (hasta) -->
            <div class="form-group col-md-3">
                <label for="fechaHasta">Fecha hasta:</label>
                <input type="date" name="fechaHasta" id="fechaHasta" class="form-control" value="{{ request('fechaHasta') }}">
            </div>
            <!-- Buscador por Nombre -->
            <div class="form-group col-md-3">
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Buscar por nombre" value="{{ request('nombre') }}">
            </div>
        </div>

        <div class="form-row">
            <!-- Elementos por página -->
            <div class="form-group col-md-3">
                <label for="pageSize">Elementos por página:</label>
                <select name="pageSize" id="pageSize" class="form-control">
                    <option value="10" {{ request('pageSize') == '10' ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('pageSize') == '20' ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('pageSize') == '50' ? 'selected' : '' }}>50</option>
                </select>
            </div>
            <div class="form-group col-md-3 align-self-end">
                <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
            </div>
        </div>
    </form>
</div>
@endsection