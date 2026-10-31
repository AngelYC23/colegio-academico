@extends('layouts.app')
@section('title', 'Resumen')
@section('content')
@if(auth()->user()->role === 'admin')
<div class="row g-3">@foreach($stats as $label => $value)<div class="col-6 col-xl-4"><div class="card metric"><span class="metric-label">{{ $label }}</span><strong class="metric-value">{{ number_format($value) }}</strong></div></div>@endforeach</div>
<div class="card mt-4"><div class="p-4"><h2 class="section-title">Actividad reciente</h2><p class="section-note">Último acceso por usuario entre los 8 eventos más recientes.</p></div>
<div class="table-responsive"><table class="table"><thead><tr><th>Usuario</th><th>Fecha y hora</th><th>Actividad</th></tr></thead><tbody>
@forelse($recent->unique('user_id') as $entry)<tr><td>{{ $users[$entry->user_id] ?? 'Usuario no disponible' }}</td><td>{{ \Illuminate\Support\Carbon::parse($entry->created_at)->timezone(config('academic.timezone'))->format('d/m/Y · H:i') }}</td><td><span class="status-pill">Inicio de sesión</span></td></tr>
@empty<tr><td colspan="3" class="empty-state"><strong>Sin actividad reciente</strong>Los accesos aparecerán aquí.</td></tr>@endforelse
</tbody></table></div></div>
@elseif(auth()->user()->role === 'teacher')<div class="card p-4"><h2 class="section-title mb-2">Tu espacio docente</h2><p class="text-muted">Consulta tus grupos y registra las notas de los períodos abiertos.</p><a href="{{ route('marks.index') }}" class="btn btn-primary align-self-start">Ver mis grupos →</a></div>
@else<div class="card p-4"><h2 class="section-title mb-2">Tu información académica</h2><p class="text-muted">Consulta las inscripciones y calificaciones disponibles para tu cuenta.</p><a href="{{ route('personal') }}" class="btn btn-primary align-self-start">Ver mi expediente →</a></div>@endif
@endsection
