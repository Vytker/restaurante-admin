{{-- filepath: c:\Users\Vytenis\Desktop\tfg\restaurante-admin\resources\views\restaurants\assign-staff.blade.php --}}
@extends('adminlte::page')

@section('title', 'Asignar Staff')

@section('content')
<div class="container mt-4">
  <div class="card">
    <div class="card-header bg-primary text-white">
      <h3 class="card-title mb-0">Asignar Staff al Restaurante</h3>
    </div>
    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success">
          {{ session('success') }}
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('restaurants.staff.assign', ['restaurantId' => $restaurantId]) }}" method="POST">
        @csrf

        <div class="form-group">
          <label for="Email">Email del empleado</label>
          <input type="text" name="Email" id="Email" value="{{ old('Email') }}" required class="form-control" placeholder="Ingrese el email">
        </div>

        <button type="submit" class="btn btn-primary">Asignar Staff</button>
      </form>
    </div>
  </div>
</div>
@endsection