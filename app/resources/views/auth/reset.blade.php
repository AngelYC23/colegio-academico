@extends('layouts.app')
@section('title', 'Restablecer contraseña')
@section('content')
<div class="row justify-content-center"><div class="col-12 card auth-card"><h1 class="h4">Restablecer contraseña</h1><form method="post" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}"><label class="form-label">Correo<input type="email" name="email" value="{{ $email }}" required class="form-control"></label><label class="form-label d-block">Contraseña nueva<input type="password" name="password" required minlength="12" class="form-control"></label><label class="form-label d-block">Confirmación<input type="password" name="password_confirmation" required class="form-control"></label><button class="btn btn-primary">Restablecer</button></form></div></div>
@endsection
