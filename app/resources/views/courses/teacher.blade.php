@extends('layouts.app')
@section('title', 'Cursos del profesor')
@section('content')
<div class="toolbar"><div><h2 class="section-title">{{ $person->first_name }} {{ $person->last_name }}</h2><p class="section-note">{{ $person->code }}</p></div>
@if(auth()->user()->role === 'admin')<a class="btn btn-primary" href="{{ route('resource.create', ['resource' => 'assignments', 'teacher_id' => $person->id]) }}">+ Asignar curso al profesor</a>@endif</div>
<div class="card"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Curso</th><th>Ciclo</th><th>Grado / sección</th><th>Alumnos inscritos</th><th>Acciones</th></tr></thead><tbody>
@forelse($courses as $course)<tr><td><strong>{{ $course->subject }}</strong><div class="small text-muted">{{ $course->code }}{{ $course->active ? '' : ' · Inactivo' }}</div></td><td>{{ $course->year }}</td><td>{{ $course->grade }} ({{ $course->level }}) · {{ $course->section }}</td><td>{{ $course->student_count }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('courses.roster', $course->id) }}">Ver alumnos</a> @if($course->active)<a class="btn btn-sm btn-outline-secondary" href="{{ route('marks.group', $course->id) }}">Calificaciones</a>@endif</td></tr>
@empty<tr><td colspan="5" class="empty-state"><strong>Sin cursos asignados</strong>Los cursos de este profesor aparecerán aquí.</td></tr>@endforelse
</tbody></table></div></div>
@if(auth()->user()->role === 'admin')<a class="btn btn-outline-secondary mt-3" href="{{ route('resource.index', 'teachers') }}">Volver a profesores</a>@endif
@endsection
