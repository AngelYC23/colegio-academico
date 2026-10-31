<details class="card p-3 mb-3"><summary class="fw-semibold">Datos de contacto y del perfil</summary>
<dl class="row small mb-0 mt-3">
<dt class="col-sm-3">Correo</dt><dd class="col-sm-9">{{ $person->email ?: 'No registrado' }}</dd>
<dt class="col-sm-3">Teléfono</dt><dd class="col-sm-9">{{ $person->phone ?: 'No registrado' }}</dd>
<dt class="col-sm-3">Dirección</dt><dd class="col-sm-9">{{ $person->address ?: 'No registrada' }}</dd>
@if(property_exists($person, 'birth_date'))<dt class="col-sm-3">Nacimiento</dt><dd class="col-sm-9">{{ $person->birth_date ? \Illuminate\Support\Carbon::parse($person->birth_date)->format('d/m/Y') : 'No registrado' }}</dd>@endif
<dt class="col-sm-3">Perfil</dt><dd class="col-sm-9">{{ $person->active ? 'Activo' : 'Inactivo' }}</dd>
</dl></details>
