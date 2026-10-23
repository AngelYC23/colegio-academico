@extends('layouts.app')
@section('title', 'Panel académico')
@section('content')
<p class="text-muted">Resumen de la actividad institucional.</p>
@if(auth()->user()->role === 'admin')<div class="row g-3">@foreach($stats as $label => $value)<div class="col-6 col-xl-4"><div class="card p-4"><span class="text-muted">{{ $label }}</span><strong class="display-6">{{ $value }}</strong></div></div>@endforeach</div>
<div class="card p-4 mt-4"><h2 class="h5">Accesos recientes</h2><ul class="mb-0">@forelse($recent as $entry)<li>{{ $users[$entry->user_id] ?? '—' }} · {{ $entry->created_at }}</li>@empty<li>Sin accesos registrados.</li>@endforelse</ul></div>
@elseif(auth()->user()->role === 'teacher')<div class="card p-4"><p>Consulte sus grupos y registre notas en períodos abiertos.</p><a href="{{ route('marks.index') }}" class="btn btn-primary align-self-start">Mis grupos</a></div>
@else<div class="card p-4"><p>Consulte la información académica disponible para su cuenta.</p><a href="{{ route('personal') }}" class="btn btn-primary align-self-start">Mi expediente</a></div>@endif
@endsection
