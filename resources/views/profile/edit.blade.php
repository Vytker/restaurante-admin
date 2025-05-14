@extends('adminlte::page')

@section('title', 'Mi perfil')

@section('content')
<div class="container">

    <h1 class="h3 mb-4">Mi perfil</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf  @method('PUT')

        <!-- E-mail solo lectura -->
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" value="{{ $profile['email'] }}" disabled>
        </div>

        <!-- Nombre -->
        <div class="mb-3">
            <label class="form-label" for="firstName">Nombre</label>
            <input id="firstName" name="firstName" class="form-control"
                   value="{{ old('firstName', $profile['firstName'] ?? '') }}">
        </div>

        <!-- Apellidos -->
        <div class="mb-3">
            <label class="form-label" for="lastName">Apellidos</label>
            <input id="lastName" name="lastName" class="form-control"
                   value="{{ old('lastName', $profile['lastName'] ?? '') }}">
        </div>

        <!-- Teléfono -->
        <div class="mb-3">
            <label class="form-label" for="telefono">Teléfono</label>
            <input id="telefono" name="telefono" class="form-control"
                   value="{{ old('telefono', $profile['telefono'] ?? '') }}">
        </div>

        <button class="btn btn-primary">Guardar</button>
    </form>
</div>
@endsection
