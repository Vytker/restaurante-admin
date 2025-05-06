{{-- filepath: resources/views/admin/create_restaurant.blade.php --}}
@extends('adminlte::page')

@section('title', 'Crear Restaurante y Owner')

@section('content')
<div class="container mt-5">
    <h2>Crear Restaurante y asignar Owner</h2>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('admin.restaurants.store') }}" method="POST">
        @csrf
        
        <h4>Datos del Restaurante</h4>
        <div class="form-group">
            <label for="Nombre">Nombre</label>
            <input type="text" name="Nombre" id="Nombre" class="form-control" value="{{ old('Nombre') }}" required>
        </div>

        <div class="form-group">
            <label for="Slug">Slug</label>
            <input type="text" name="Slug" id="Slug" class="form-control" value="{{ old('Slug') }}" required>
        </div>
        
        <h4>Datos del Owner</h4>
        <div class="form-group">
            <label for="OwnerUserName">UserName</label>
            <input type="text" name="Owner[UserName]" id="OwnerUserName" class="form-control" value="{{ old('Owner.UserName') }}" required>
        </div>
        
        <div class="form-group">
            <label for="OwnerFirstName">First Name</label>
            <input type="text" name="Owner[FirstName]" id="OwnerFirstName" class="form-control" value="{{ old('Owner.FirstName') }}" required>
        </div>
        
        <div class="form-group">
            <label for="OwnerLastName">Last Name</label>
            <input type="text" name="Owner[LastName]" id="OwnerLastName" class="form-control" value="{{ old('Owner.LastName') }}" required>
        </div>
        
        <div class="form-group">
            <label for="OwnerEmail">Email</label>
            <input type="email" name="Owner[Email]" id="OwnerEmail" class="form-control" value="{{ old('Owner.Email') }}" required>
        </div>
        
        <div class="form-group">
            <label for="OwnerPassword">Password</label>
            <input type="password" name="Owner[Password]" id="OwnerPassword" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="OwnerPassword_confirmation">Confirm Password</label>
            <input type="password" name="Owner[Password_confirmation]" id="OwnerPassword_confirmation" class="form-control" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Crear Restaurante y Owner</button>
    </form>
</div>
@endsection