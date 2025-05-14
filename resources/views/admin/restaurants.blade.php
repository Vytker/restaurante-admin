@extends('adminlte::page')

@section('title', 'Admin - Restaurantes')

@section('content')
    <h1>Restaurantes registrados</h1>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first('error') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Otros Datos</th>
            </tr>
        </thead>
        <tbody>
              @foreach ($restaurants as $restaurant)
                <tr>
                    <td>{{ $restaurant['id'] }}</td>
                    <td>{{ $restaurant['nombre'] }}</td>
                    <td>{{ $restaurant['slug'] ?? '-' }}</td>
                    <td>
                        @if(session('restaurante_id') == $restaurant['id'])
                            <!-- Si ya está seleccionado, mostraremos el botón para deseleccionar -->
                            <form action="{{ route('admin.deselectRestaurant') }}" method="POST">
                                @csrf
                                <input type="hidden" name="restaurant_id" value="{{ $restaurant['id'] }}">
                                <button type="submit" class="btn btn-warning">Deseleccionar</button>
                            </form>
                        @else
                            <!-- Si no está seleccionado, el botón para seleccionar -->
                            <form action="{{ route('admin.selectRestaurant') }}" method="POST">
                                @csrf
                                <input type="hidden" name="restaurant_id" value="{{ $restaurant['id'] }}">
                                <button type="submit" class="btn btn-primary">Seleccionar</button>
                            </form>
                        @endif
                    </td>
                    
                </tr>
            @endforeach
            
        </tbody>
    </table>
    
@endsection