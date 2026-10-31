@extends('layouts.app')
@section('title', 'Recuperar contraseña')
@section('content')
<div class="row justify-content-center"><div class="col-12 card auth-card"><h1 class="h4">Recuperar contraseña</h1><p>Se requiere un servicio de correo configurado para recibir el enlace.</p><form method="post" action="{{ route('password.email') }}">@csrf<label for="email" class="form-label">Correo</label><input required type="email" id="email" name="email" class="form-control mb-3"><button class="btn btn-primary">Enviar enlace</button></form><a href="{{ route('login') }}">Regresar</a></div></div>
@endsection
