@extends('layouts.app')
@section('title', 'Mi expediente académico')
@section('content')
<div class="card p-4"><h2 class="h5">Inscripciones</h2><div class="table-responsive"><table class="table"><thead><tr><th>Estudiante</th><th>Ciclo</th><th>Grado</th><th>Sección</th></tr></thead><tbody>@forelse($enrollments as $item)<tr><td>{{ $item->first_name }} {{ $item->last_name }} ({{ $item->code }})</td><td>{{ $item->year }}</td><td>{{ $item->grade }} ({{ $item->level }})</td><td>{{ $item->section }}</td></tr>@empty<tr><td colspan="4">No hay inscripciones.</td></tr>@endforelse</tbody></table></div></div>
<div class="card p-4 mt-3"><h2 class="h5">Calificaciones</h2><div class="table-responsive"><table class="table"><thead><tr><th>Inscripción</th><th>Asignatura</th><th>Período</th><th>Nota</th></tr></thead><tbody>@forelse($marks as $mark)<tr><td>{{ $mark->first_name }} {{ $mark->last_name }} ({{ $mark->code }}) · {{ $mark->year }}</td><td>{{ $mark->subject }}</td><td>{{ $mark->period }}</td><td>{{ $mark->score }}</td></tr>@empty<tr><td colspan="4">No hay calificaciones.</td></tr>@endforelse</tbody></table></div></div>
@endsection
