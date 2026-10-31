@extends('layouts.app')
@section('title', 'Calificaciones del grupo')
@section('content')
<p class="text-muted">Las notas solo se pueden modificar durante las fechas de un período abierto. Escala: {{ config('academic.min_score') }}–{{ config('academic.max_score') }}.</p>
<div class="card p-3"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Estudiante</th>@foreach($periods as $period)<th>{{ $period->name }}</th>@endforeach</tr></thead><tbody>
@forelse($students as $student)<tr><td>{{ $student->code }} · {{ $student->first_name }} {{ $student->last_name }}</td>
@foreach($periods as $period)@php($mark = $marks->get($student->id . '-' . $period->id))<td>
@if($cycleActive && $period->open && $period->start_date <= now(config('academic.timezone'))->toDateString() && $period->end_date >= now(config('academic.timezone'))->toDateString())
<form method="post" action="{{ route('marks.save', $group->id) }}" class="d-flex gap-1">@csrf<input type="hidden" name="enrollment_id" value="{{ $student->id }}"><input type="hidden" name="period_id" value="{{ $period->id }}"><input type="number" step="0.01" min="{{ config('academic.min_score') }}" max="{{ config('academic.max_score') }}" name="score" aria-label="Nota de {{ $student->first_name }}" value="{{ $mark->score ?? '' }}" class="form-control form-control-sm" required><input type="text" name="observation" aria-label="Observación" placeholder="Observación" value="{{ $mark->observation ?? '' }}" class="form-control form-control-sm"><button class="btn btn-primary btn-sm">Guardar</button></form>
@else{{ $mark->score ?? '—' }}@endif</td>@endforeach</tr>
@empty<tr><td colspan="{{ $periods->count() + 1 }}">No hay alumnos inscritos en este grupo.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
