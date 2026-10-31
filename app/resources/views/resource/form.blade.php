@extends('layouts.app')
@section('title', ($row ? 'Editar ' : 'Registrar ') . strtolower($title))
@section('content')
<div class="card p-4"><form method="post" action="{{ $row ? route('resource.update', [$resource, $row->id]) : route('resource.store', $resource) }}">
@csrf
@if($row) @method('PUT') @endif
<p class="text-muted small mb-4">Los campos marcados con * son obligatorios.</p>
<div class="row g-3">
@foreach($fields as $key => $label)
@php
    $required = $resource === 'users' && $key === 'email' || !in_array($key, \App\Support\InputRules::OPTIONAL);
    $value = old($key, $row->$key ?? '');
    $invalid = $errors->has($key) ? ' is-invalid' : '';
    $isDate = in_array($key, ['birth_date', 'start_date', 'end_date', 'enrolled_at']);
    $isNumber = in_array($key, ['year', 'capacity', 'percentage']);
@endphp
<div class="col-sm-6">
<label for="field-{{ $key }}" class="form-label">{{ $label }} @if($required)<span aria-hidden="true">*</span>@endif</label>
@if(isset($options[$key]))
<select class="form-select{{ $invalid }}" id="field-{{ $key }}" name="{{ $key }}" @required($required)>
<option value="">Seleccione</option>
@foreach($options[$key] as $option => $text)<option value="{{ $option }}" @selected($value == $option)>{{ $text }}</option>@endforeach
</select>
@elseif($key === 'role')
<select class="form-select{{ $invalid }}" id="field-{{ $key }}" name="{{ $key }}" required>
@foreach(['admin' => 'Administrador', 'teacher' => 'Profesor', 'student' => 'Estudiante', 'guardian' => 'Encargado'] as $option => $text)<option value="{{ $option }}" @selected($value === $option)>{{ $text }}</option>@endforeach
</select>
@elseif(in_array($key, ['active', 'open', 'primary']))
<select class="form-select{{ $invalid }}" id="field-{{ $key }}" name="{{ $key }}" required>
<option value="1" @selected(old($key, $row->$key ?? 1) == 1)>Sí</option><option value="0" @selected(old($key, $row->$key ?? 1) == 0)>No</option>
</select>
@else
<input class="form-control{{ $invalid }}" id="field-{{ $key }}" name="{{ $key }}" value="{{ $value }}"
    type="{{ $isDate ? 'date' : ($key === 'email' ? 'email' : ($key === 'phone' ? 'tel' : ($isNumber ? 'number' : 'text'))) }}" @required($required)
    @if($key === 'percentage') step="0.01" min="0" max="100"
    @elseif($key === 'year') min="1900" max="9999" step="1"
    @elseif($key === 'capacity') min="1" max="9999" step="1"
    @elseif(in_array($key, ['birth_date', 'enrolled_at'])) max="{{ now(config('academic.timezone'))->toDateString() }}" @if($key === 'birth_date') min="1900-01-01" @endif
    @endif
    @if(!$isDate && !$isNumber) maxlength="{{ $key === 'email' ? 254 : ($key === 'phone' || $key === 'code' ? 30 : ($key === 'sex' ? 20 : ($key === 'username' ? 50 : (in_array($key, ['first_name', 'last_name']) || $resource === 'users' && $key === 'name' ? 100 : 255)))) }}" @endif
    @if($key === 'email') autocomplete="email" placeholder="nombre@dominio.com" @elseif($key === 'phone') autocomplete="tel" placeholder="5555-1234 o +502 5555-1234" @endif>
@endif
@error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
@if($key === 'email')<div class="form-text">Un solo @ y dominio completo. El formato no confirma que el buzón exista.</div>@endif
</div>
@endforeach
@if($resource === 'users')
<div class="col-sm-6"><label class="form-label" for="password">Contraseña {{ $row ? '(dejar vacía para conservar)' : '(mínimo 12 caracteres) *' }}</label><input class="form-control" type="password" id="password" name="password" minlength="12" autocomplete="new-password" @required(!$row)></div>
<div class="col-sm-6"><label class="form-label" for="password_confirmation">Confirmar contraseña</label><input class="form-control" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" @required(!$row)></div>
@endif
</div>
<div class="mt-4"><button class="btn btn-primary">Guardar</button> <a class="btn btn-outline-secondary" href="{{ route('resource.index', $resource) }}">Cancelar</a></div>
</form></div>
@endsection
