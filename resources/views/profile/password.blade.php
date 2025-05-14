@extends('adminlte::page')

@section('title', 'Cambiar contraseña')

@section('content')
<div class="container">
    <h1 class="h3 mb-4">Cambiar contraseña</h1>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())     <div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <form id="passwordForm" method="POST" action="{{ route('password.update') }}">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label" for="passwordActual">Contraseña actual</label>
            <input id="passwordActual" name="passwordActual" type="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label" for="passwordNueva">Nueva contraseña</label>
            <input id="passwordNueva" name="passwordNueva" type="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label" for="passwordNuevaConfirm">Repite nueva contraseña</label>
            <input id="passwordNuevaConfirm" name="passwordNuevaConfirm" type="password"
                   class="form-control" required>
            <div id="pwError" class="invalid-feedback">Las contraseñas no coinciden.</div>
        </div>

        <button class="btn btn-primary" type="submit">Cambiar contraseña</button>
    </form>
</div>

@push('js')
<script>
document.getElementById('passwordForm').addEventListener('submit', function (e) {
    const a = document.getElementById('passwordNueva');
    const b = document.getElementById('passwordNuevaConfirm');
    const msg = document.getElementById('pwError');

    if (a.value !== b.value) {
        e.preventDefault();
        b.classList.add('is-invalid'); msg.style.display = 'block';
    } else {
        b.classList.remove('is-invalid'); msg.style.display = 'none';
    }
});
</script>
@endpush
@endsection
