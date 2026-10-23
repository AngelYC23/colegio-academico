@extends('layouts.app')
@section('title', 'Cambiar contraseña')
@section('content')
<div class="card p-4 col-lg-6"><form method="post" action="{{ route('password.change.save') }}">@csrf @method('PUT')
<label class="form-label d-block">Contraseña actual<input type="password" name="current_password" class="form-control" required></label>
<label class="form-label d-block">Contraseña nueva (mínimo 12 caracteres)<input type="password" name="password" class="form-control" required minlength="12"></label>
<label class="form-label d-block">Confirmar contraseña<input type="password" name="password_confirmation" class="form-control" required></label>
<button class="btn btn-primary">Actualizar contraseña</button></form></div>
@endsection
