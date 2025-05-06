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
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection