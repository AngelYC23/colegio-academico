@extends('layouts.app')
@section('title', 'Iniciar sesión')
@section('content')
<div class="card auth-card">
<div class="mb-4"><div class="eyebrow">Bienvenido</div><h1>Iniciar sesión</h1><p class="text-muted mb-0 mt-2">Ingresa con tu cuenta institucional.</p></div>
<form method="post" action="{{ route('login.submit') }}">@csrf
<label class="form-label" for="identity">Usuario o correo electrónico</label><input required autofocus autocomplete="username" class="form-control mb-3" name="identity" id="identity" value="{{ old('identity') }}">
<label class="form-label" for="password">Contraseña</label><input required type="password" autocomplete="current-password" class="form-control mb-4" name="password" id="password">
<button class="btn btn-primary w-100">Ingresar →</button></form>
<a class="mt-4 text-center small text-decoration-none" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
</div>
@endsection
