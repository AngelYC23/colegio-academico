@extends('layouts.app')
@section('title', 'Cursos del estudiante')
@section('content')
<div class="toolbar"><div><h2 class="section-title">{{ $person->first_name }} {{ $person->last_name }}</h2><p class="section-note">{{ $person->code }}</p></div><a class="btn btn-outline-secondary" href="{{ route('resource.index', 'students') }}">Volver a estudiantes</a></div>
@if($enrollments->isNotEmpty())
<form method="get" class="card p-3 mb-3"><label class="form-label" for="enrollment">Inscripción y ciclo escolar</label><div class="d-flex gap-2"><select class="form-select" id="enrollment" name="enrollment">
@foreach($enrollments as $item)<option value="{{ $item->id }}" @selected($enrollment?->id === $item->id)>{{ $item->year }} · {{ $item->grade }} ({{ $item->level }}) · Sección {{ $item->section }}{{ $item->active ? '' : ' · Inscripción inactiva' }}</option>@endforeach
</select><button class="btn btn-outline-primary">Consultar</button></div></form>
@if(!$canEdit)<div class="alert alert-info">Consulta de cursos. Para cambiar la selección, el estudiante, su inscripción, ciclo, grado y sección deben estar activos.</div>@endif
<form method="post" action="{{ route('courses.student.save', $person->id) }}" class="card">
@csrf @method('PUT')
<input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}"><input type="hidden" name="_course_form" value="1">
<div class="p-4"><h2 class="section-title">Selección individual de cursos</h2><p class="section-note">Marca los cursos que llevará este alumno. Se muestran las ofertas de su ciclo, grado y sección; cada alumno puede tener una selección distinta.</p></div>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Inscrito</th><th>Curso</th><th>Profesor</th><th>Estado</th></tr></thead><tbody>
@php
$chosen = session()->hasOldInput('_course_form') ? old('assignment_ids', []) : $selected;
$chosen = is_array($chosen) ? $chosen : [];
@endphp
@forelse($courses as $course)
@php
$hasMarks = in_array($course->id, $locked);
$available = $course->active && $course->subject_active && $course->teacher_active;
@endphp
<tr><td>
<input class="form-check-input" type="checkbox" id="course-{{ $course->id }}" name="assignment_ids[]" value="{{ $course->id }}" @checked($hasMarks || in_array($course->id, $chosen)) @disabled(!$canEdit || $hasMarks) aria-label="Inscribir en {{ $course->subject }}">
@if($hasMarks)<input type="hidden" name="assignment_ids[]" value="{{ $course->id }}">@endif
</td><td><label for="course-{{ $course->id }}" class="fw-semibold">{{ $course->subject }}</label><div class="small text-muted">{{ $course->code }}</div></td><td>{{ $course->first_name }} {{ $course->last_name }}</td><td>
@if($hasMarks)<span class="status-pill">Con calificaciones</span>@elseif(!$available)<span class="status-pill muted">Oferta inactiva</span>@else<span class="text-muted small">Disponible</span>@endif
</td></tr>
@empty<tr><td colspan="4" class="empty-state"><strong>No hay cursos ofrecidos para este grupo.</strong>Registra una asignación docente para este ciclo, grado y sección.</td></tr>@endforelse
</tbody></table></div>
@if($canEdit)<div class="p-4"><button class="btn btn-primary">Guardar cursos del estudiante</button><p class="section-note mt-2">Los cursos con calificaciones se conservan para proteger el historial.</p></div>@endif
</form>
@if(auth()->user()->role === 'admin')<p class="small mt-3"><a href="{{ route('resource.create', ['resource' => 'assignments', 'school_year_id' => $enrollment->school_year_id, 'grade_id' => $enrollment->grade_id, 'section_id' => $enrollment->section_id]) }}">Registrar una nueva oferta de curso</a></p>@endif
@else
<div class="card empty-state"><strong>Este estudiante aún no tiene una inscripción.</strong><p>Primero indica su ciclo, grado y sección.</p><a class="btn btn-primary align-self-center" href="{{ route('resource.create', ['resource' => 'enrollments', 'student_id' => $person->id]) }}">Inscribir estudiante</a></div>
@endif
@endsection
