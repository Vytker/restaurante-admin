@extends('layouts.minimal')

@section('content')
  <h1>Completar perfil</h1>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(!isset($completed))
    <form method="POST" action="{{ url('/complete-profile') }}">
      @csrf
      <input type="hidden" name="invite_token" value="{{ $inviteToken }}">

      <div class="form-group">
        <label for="username">Usuario</label>
        <input
          id="username"
          name="username"
          type="text"
          class="form-control"
          value="{{ old('username') }}"
          required
        >
        @error('username') <small class="text-danger">{{ $message }}</small> @enderror
      </div>

      <div class="form-group">
        <label for="first_name">Nombre</label>
        <input
          id="first_name"
          name="first_name"
          type="text"
          class="form-control"
          value="{{ old('first_name') }}"
          required
        >
        @error('first_name') <small class="text-danger">{{ $message }}</small> @enderror
      </div>

      <div class="form-group">
        <label for="last_name">Apellidos</label>
        <input
          id="last_name"
          name="last_name"
          type="text"
          class="form-control"
          value="{{ old('last_name') }}"
          required
        >
        @error('last_name') <small class="text-danger">{{ $message }}</small> @enderror
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <input
          id="password"
          name="password"
          type="password"
          class="form-control"
          required
        >
        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
      </div>

      <div class="form-group">
        <label for="password_confirmation">Confirmar contraseña</label>
        <input
          id="password_confirmation"
          name="password_confirmation"
          type="password"
          class="form-control"
          required
        >
      </div>

      <button type="submit" class="btn btn-primary">Completar perfil</button>
    </form>
  @else
    <p>¡Tu perfil se ha completado! <a href="{{ route('login') }}">Ir al login</a></p>
  @endif
@endsection
