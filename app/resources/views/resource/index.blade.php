@extends('layouts.app')
@section('title', $title)
@section('content')
@php
$listFields = array_diff_key($fields, array_flip(['birth_date', 'sex', 'address', 'description', 'user_id']));
$roles = ['admin' => 'Administrador', 'teacher' => 'Profesor', 'student' => 'Estudiante', 'guardian' => 'Encargado'];
@endphp
<div class="toolbar"><span class="text-muted small">{{ number_format($rows->total()) }} {{ $rows->total() === 1 ? 'registro' : 'registros' }}</span><a class="btn btn-primary" href="{{ route('resource.create', $resource) }}">+ Agregar</a></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr>@foreach($listFields as $label)<th scope="col">{{ $label }}</th>@endforeach<th scope="col"><span class="visually-hidden">Acciones</span></th></tr></thead><tbody>
@forelse($rows as $row)<tr>@foreach($listFields as $key => $label)<td>
@if(in_array($key, ['active', 'primary', 'open']))<span @class(['status-pill', 'muted' => !$row->$key])>{{ $key === 'active' ? ($row->$key ? 'Activo' : 'Inactivo') : ($key === 'open' ? ($row->$key ? 'Abierto' : 'Cerrado') : ($row->$key ? 'Sí' : 'No')) }}</span>
@elseif($key === 'role'){{ $roles[$row->$key] ?? $row->$key }}
@else{{ isset($related[$key]) ? ($related[$key][$row->$key] ?? '—') : ($row->$key ?: '—') }}@endif
</td>@endforeach<td class="text-end">
@if($resource === 'students')<a class="btn btn-sm btn-outline-primary" href="{{ route('courses.student', $row->id) }}">Cursos</a>
@elseif($resource === 'teachers')<a class="btn btn-sm btn-outline-primary" href="{{ route('courses.teacher', $row->id) }}">Cursos y alumnos</a>
@elseif($resource === 'enrollments')<a class="btn btn-sm btn-outline-primary" href="{{ route('courses.student', ['student' => $row->student_id, 'enrollment' => $row->id]) }}">Cursos</a>
@elseif($resource === 'assignments')<a class="btn btn-sm btn-outline-primary" href="{{ route('courses.roster', $row->id) }}">Alumnos</a>
@endif
<a class="btn btn-sm btn-outline-primary" aria-label="Editar registro {{ $row->id }}" href="{{ route('resource.edit', [$resource, $row->id]) }}">Editar</a></td></tr>
@empty<tr><td colspan="{{ count($listFields) + 1 }}" class="empty-state"><strong>Aún no hay registros</strong>Usa «Agregar» para crear el primero.</td></tr>@endforelse
</tbody></table></div></div>{{ $rows->links() }}
@endsection
