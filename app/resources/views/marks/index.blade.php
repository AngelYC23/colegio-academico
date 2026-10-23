@extends('layouts.app')
@section('title', 'Calificaciones · grupos')
@section('content')
<div class="card p-4"><div class="list-group">@forelse($assignments as $group)
<a class="list-group-item list-group-item-action" href="{{ route('marks.group', $group->id) }}">{{ $group->subject }} · {{ $group->grade }} ({{ $group->level }}) · Sección {{ $group->section }} · Ciclo {{ $group->year }}</a>
@empty<p>No hay grupos asignados.</p>@endforelse</div></div>
@endsection
