@extends('layouts.app')
@section('title', 'Informes')
@section('content')
<nav class="report-tabs" aria-label="Tipo de informe">
@foreach(['enrollments' => 'Inscripciones', 'teachers' => 'Profesores', 'assignments' => 'Asignaciones', 'marks' => 'Calificaciones'] as $key => $label)
<a href="{{ route('report', $key) }}" @class(['active' => $name === $key]) @if($name === $key) aria-current="page" @endif>{{ $label }}</a>
@endforeach
</nav>
@php
$filterLabels = match ($name) {
'enrollments' => ['cycle' => 'Ciclo', 'grade' => 'Grado', 'section' => 'Sección', 'student' => 'Estudiante'],
'assignments' => ['cycle' => 'Ciclo', 'grade' => 'Grado', 'section' => 'Sección', 'subject' => 'Asignatura'],
'marks' => ['cycle' => 'Ciclo', 'student' => 'Estudiante', 'subject' => 'Asignatura'],
default => [],
};
$columnLabels = ['code' => 'Código', 'first_name' => 'Nombres', 'last_name' => 'Apellidos', 'year' => 'Ciclo', 'grade' => 'Grado', 'level' => 'Nivel', 'section' => 'Sección', 'email' => 'Correo', 'phone' => 'Teléfono', 'subject_code' => 'Código de asignatura', 'subject' => 'Asignatura', 'period' => 'Período', 'score' => 'Nota'];
@endphp
@if($name !== 'teachers')
    <form method="get" class="card p-3 mb-3">
        <div class="row g-2">
            @foreach($filterLabels as $key => $label)
                <div class="col-sm-4 col-lg-2">
                    <label class="form-label" for="{{ $key }}">{{ $label }}</label>
                    <select class="form-select" id="{{ $key }}" name="{{ $key }}">
                        <option value="">Todos</option>
                        @if(request($key) && !isset($filters[$key][request($key)]))
                            <option value="{{ request($key) }}" selected>Opción no disponible</option>
                        @endif
                        @foreach($filters[$key] as $id => $text)
                            <option value="{{ $id }}" @selected(request($key) == $id)>{{ $text }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="col-sm-4 col-lg-2 align-self-end"><button class="btn btn-primary w-100">Filtrar</button></div>
        </div>
    </form>
@endif
<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    @if($rows->count())
                        @foreach(array_keys((array) $rows->first()) as $column)
                            <th>{{ $columnLabels[$column] ?? $column }}</th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        @foreach((array) $row as $value)
                            <td>{{ $value }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td class="empty-state"><strong>Sin resultados</strong>No hay datos para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $rows->withQueryString()->links() }}
</div>
@endsection
