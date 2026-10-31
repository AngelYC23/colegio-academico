@extends('layouts.app')
@section('title', 'Alumnos del curso')
@section('content')
<div class="toolbar"><div><h2 class="section-title">{{ $course->subject }}</h2><p class="section-note">{{ $course->year }} · {{ $course->grade }} ({{ $course->level }}) · Sección {{ $course->section }}<br>Profesor: {{ $course->first_name }} {{ $course->last_name }}</p></div>
<a class="btn btn-outline-secondary" href="{{ route('courses.teacher', $course->teacher_id) }}">Cursos del profesor</a></div>
@include('partials.search-filters', ['searchPlaceholder' => 'Nombre, apellido o código del alumno'])
<div class="card"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Código</th><th>Estudiante</th>@if(auth()->user()->role === 'admin')<th>Acción</th>@endif</tr></thead><tbody>
@forelse($students as $student)<tr><td>{{ $student->code }}</td><td>{{ $student->first_name }} {{ $student->last_name }}</td>@if(auth()->user()->role === 'admin')<td><a class="btn btn-sm btn-outline-primary" href="{{ route('courses.student', $student->id) }}">Ver sus cursos</a></td>@endif</tr>
@empty<tr><td colspan="3" class="empty-state"><strong>{{ $searchQuery !== '' ? 'No hay alumnos que coincidan con la búsqueda.' : 'No hay alumnos inscritos en este curso.' }}</strong>{{ $searchQuery !== '' ? 'Prueba otro nombre o limpia la búsqueda.' : 'El administrador puede seleccionarlo desde Estudiantes → Cursos.' }}</td></tr>@endforelse
</tbody></table></div></div>
@if(auth()->user()->role === 'admin')<a class="btn btn-primary mt-3" href="{{ route('resource.index', 'students') }}">Asignar cursos a estudiantes</a>@endif
@endsection
