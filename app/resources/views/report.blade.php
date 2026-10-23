@extends('layouts.app')
@section('title', 'Informe: ' . $name)
@section('content')
@if($name !== 'teachers')
    <form method="get" class="card p-3 mb-3">
        <div class="row g-2">
            @foreach(['cycle' => 'Ciclo', 'grade' => 'Grado', 'section' => 'Sección', 'student' => 'Estudiante', 'subject' => 'Asignatura'] as $key => $label)
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
                            <th>{{ ucfirst(str_replace('_', ' ', $column)) }}</th>
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
                    <tr><td>Sin datos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $rows->withQueryString()->links() }}
</div>
@endsection
