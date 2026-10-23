@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="d-flex justify-content-end mb-3"><a class="btn btn-primary" href="{{ route('resource.create', $resource) }}">Agregar registro</a></div>
<div class="card p-3"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>ID</th>@foreach($fields as $label)<th>{{ $label }}</th>@endforeach<th>Acción</th></tr></thead><tbody>
@forelse($rows as $row)<tr><td>{{ $row->id }}</td>@foreach($fields as $key => $label)<td>{{ isset($related[$key]) ? ($related[$key][$row->$key] ?? '—') : (in_array($key, ['active', 'primary', 'open']) ? ($row->$key ? 'Sí' : 'No') : $row->$key) }}</td>@endforeach<td><a class="btn btn-sm btn-outline-primary" href="{{ route('resource.edit', [$resource, $row->id]) }}">Editar</a></td></tr>
@empty<tr><td colspan="{{ count($fields) + 2 }}">Sin registros todavía.</td></tr>@endforelse</tbody></table></div>{{ $rows->links() }}</div>
@endsection
