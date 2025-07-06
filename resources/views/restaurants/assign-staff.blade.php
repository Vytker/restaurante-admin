{{-- filepath: c:\Users\Vytenis\Desktop\tfg\restaurante-admin\resources\views\restaurants/assign-staff.blade.php --}}
@extends('adminlte::page')

@section('title', 'Asignar Staff')

@section('content')
<div class="container mt-4">
  <div class="card shadow-sm">
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

      <form action="{{ route('restaurants.staff.assign', $restaurantId) }}" method="POST">
        @csrf
        <div class="mb-3">
          <label for="email" class="form-label">Email del nuevo staff</label>
          <input 
            type="email" 
            name="email" 
            id="email" 
            class="form-control" 
            placeholder="Ingrese el correo del nuevo staff" 
            value="{{ old('email') }}" 
            required>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-user-plus me-1"></i> Asignar Staff
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection