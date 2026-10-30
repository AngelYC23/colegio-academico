@extends('layouts.app')
@section('title', ($row ? 'Editar ' : 'Registrar ') . strtolower($title))
@section('content')
<div class="card p-4"><form method="post" action="{{ $row ? route('resource.update', [$resource, $row->id]) : route('resource.store', $resource) }}">@csrf @if($row)@method('PUT')@endif
<div class="row g-3">@foreach($fields as $key => $label)
<div class="col-sm-6"><label for="field-{{ $key }}" class="form-label">{{ $label }}</label>
@if(isset($options[$key]))<select class="form-select" id="field-{{ $key }}" name="{{ $key }}"><option value="">Seleccione</option>@foreach($options[$key] as $value => $text)<option value="{{ $value }}" @selected(old($key, $row->$key ?? '') == $value)>{{ $text }}</option>@endforeach</select>
@elseif($key === 'role')<select class="form-select" id="field-{{ $key }}" name="{{ $key }}">@foreach(['admin' => 'Administrador', 'teacher' => 'Profesor', 'student' => 'Estudiante', 'guardian' => 'Encargado'] as $value => $text)<option value="{{ $value }}" @selected(old($key, $row->$key ?? '') === $value)>{{ $text }}</option>@endforeach</select>
@elseif(in_array($key, ['active', 'open', 'primary']))<select class="form-select" id="field-{{ $key }}" name="{{ $key }}"><option value="1" @selected(old($key, $row->$key ?? 1) == 1)>Sí</option><option value="0" @selected(old($key, $row->$key ?? 1) == 0)>No</option></select>
@else<input class="form-control" id="field-{{ $key }}" name="{{ $key }}" value="{{ old($key, $row->$key ?? '') }}" type="{{ str_contains($key, 'date') || $key === 'enrolled_at' ? 'date' : ($key === 'email' ? 'email' : (in_array($key, ['year', 'capacity', 'percentage']) ? 'number' : 'text')) }}" @if($key === 'percentage') step="0.01" min="0" max="100" @elseif(in_array($key, ['year', 'capacity'])) min="1" max="9999" @endif>@endif</div>
@endforeach
@if($resource === 'users')<div class="col-sm-6"><label class="form-label" for="password">Contraseña {{ $row ? '(dejar vacía para conservar)' : '(mínimo 12 caracteres)' }}</label><input class="form-control" type="password" id="password" name="password" minlength="12"></div><div class="col-sm-6"><label class="form-label" for="password_confirmation">Confirmar contraseña</label><input class="form-control" type="password" id="password_confirmation" name="password_confirmation"></div>@endif
</div><div class="mt-4"><button class="btn btn-primary">Guardar</button> <a class="btn btn-outline-secondary" href="{{ route('resource.index', $resource) }}">Cancelar</a></div></form></div>
@endsection
