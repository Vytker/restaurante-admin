@extends('adminlte::page')

@section('title', 'Crear cuenta')

@section('content')
<div class="d-flex justify-content-center mt-5">
    <form action="{{ route('register.post') }}" method="POST" class="card p-4" style="width: 450px;">
        @csrf
        <h4 class="mb-3 text-center">Registro</h4>

        <x-adminlte-input name="username" label="Usuario" required />
        <x-adminlte-input name="email" label="Email" type="email" required />
        <x-adminlte-input name="password" label="Contraseña" type="password" required minlength="6" />
        <x-adminlte-input name="password_confirmation" label="Repetir contraseña" type="password" required minlength="6" />

        @error('auth') <p class="text-danger">{{ $message }}</p>@enderror

        <x-adminlte-button label="Crear cuenta" class="btn-block" type="submit"/>
        <p class="text-center mt-2"><a href="{{ route('login') }}">¿Ya tienes cuenta? Inicia sesión</a></p>
    </form>
</div>
@endsection
