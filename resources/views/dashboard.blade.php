{{-- resources/views/dashboard.blade.php --}}
@extends('adminlte::page')

@section('title', 'Panel')

@section('content_header')
    <h1>Bienvenido {{session('unique_name')}} 👋</h1>
@endsection

@section('content')
    <p>¡Login OK! (JWT guardado en sesión)</p>
    <p>Restaurante ID: {{ session('restaurante_id') }}</p>
    <p>Role: {{ session('role') }}</p>

@endsection
