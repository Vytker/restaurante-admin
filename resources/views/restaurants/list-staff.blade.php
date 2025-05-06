{{-- filepath: c:\Users\Vytenis\Desktop\tfg\restaurante-admin\resources\views\restaurants\list-staff.blade.php --}}
@extends('adminlte::page')

@section('title', 'Staff del Restaurante')

@section('content')
    <h1>Staff del Restaurante</h1>
    <p>ID del Restaurante: {{ $restaurantId }}</p>

    @if($errors->any())
      <div class="alert alert-danger">
         <ul>
         @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
         @endforeach
         </ul>
      </div>
    @endif

    @if(count($staff) > 0)
    <table class="table table-bordered">
      <thead>
         <tr>
            <th>ID</th>
            <th>Nombre de Usuario</th>
            <th>Email</th>
            <th>Nombre Completo</th>
         </tr>
      </thead>
      <tbody>
        @foreach($staff as $member)
         <tr>
             <td>{{ $member['id'] }}</td>
             <td>{{ $member['userName'] }}</td>
             <td>{{ $member['email'] }}</td>
             <td>{{ $member['fullName'] }}</td>
         </tr>
        @endforeach
      </tbody>
    </table>
    @else
       <p>No se encontró staff registrado para este restaurante.</p>
    @endif
@endsection