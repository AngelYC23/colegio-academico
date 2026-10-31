@extends('layouts.app')
@section('title', 'Calificaciones · grupos')
@section('content')
@include('partials.search-filters', ['searchPlaceholder' => 'Curso, grado o sección'])
<div class="card p-4"><div class="list-group">@forelse($assignments as $group)
<a class="list-group-item list-group-item-action" href="{{ route('marks.group', $group->id) }}">{{ $group->subject }} · {{ $group->grade }} ({{ $group->level }}) · Sección {{ $group->section }} · Ciclo {{ $group->year }}</a><a class="small px-3 py-2" href="{{ route('courses.roster', $group->id) }}">Ver alumnos inscritos en este curso</a>
@empty<p>No hay cursos que coincidan con los filtros seleccionados.</p>@endforelse</div></div>
@endsection
