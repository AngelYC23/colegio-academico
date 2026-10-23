@extends('layouts.app')
@section('title', 'Iniciar sesión')
@section('content')
<div class="row justify-content-center align-items-center" style="min-height:75vh"><div class="col-sm-9 col-md-6 col-lg-4"><div class="card p-4 p-md-5">
<div class="text-center mb-4"><div class="display-5 text-primary">✦</div><h1 class="h3">Colegio · Académico</h1><p class="text-muted">Acceda con su cuenta institucional</p></div>
<form method="post" action="{{ route('login.submit') }}">@csrf
<label class="form-label" for="identity">Usuario o correo</label><input required autofocus class="form-control mb-3" name="identity" id="identity" value="{{ old('identity') }}">
<label class="form-label" for="password">Contraseña</label><input required type="password" class="form-control mb-4" name="password" id="password">
<button class="btn btn-primary w-100">Iniciar sesión</button></form><a class="mt-3 text-center" href="{{ route('password.request') }}">Recuperar contraseña</a>
</div></div></div>
@endsection
