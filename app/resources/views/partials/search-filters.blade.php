<form method="get" action="{{ url()->current() }}" class="card p-3 mb-3" role="search" aria-label="Buscar registros">
<div class="row g-2 align-items-end">
<div class="{{ isset($cycles) && $cycles->isNotEmpty() ? 'col-md-6' : 'col-md-8' }}">
<label for="search-q" class="form-label">Buscar</label>
<input type="search" class="form-control" id="search-q" name="q" maxlength="100" value="{{ $searchQuery }}" placeholder="{{ $searchPlaceholder ?? 'Nombre, apellido, código o correo' }}">
</div>
@if(isset($cycles) && $cycles->isNotEmpty())
<div class="col-md-3"><label for="search-cycle" class="form-label">Ciclo escolar</label><select class="form-select" id="search-cycle" name="cycle"><option value="">Todos los ciclos</option>
@foreach($cycles as $cycle)<option value="{{ $cycle->id }}" @selected(($cycleId ?? 0) === (int) $cycle->id)>{{ $cycle->year }}{{ !$cycle->active ? ' · Cerrado' : ($cycle->year > now(config('academic.timezone'))->year ? ' · Próximo' : '') }}</option>@endforeach
</select></div>
@endif
<div class="col-md-3 d-flex gap-2"><button class="btn btn-primary">Buscar</button><a class="btn btn-outline-secondary" href="{{ url()->current() }}">Limpiar</a></div>
</div></form>
