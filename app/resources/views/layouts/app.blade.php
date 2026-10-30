<!doctype html>
<html lang="es"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Gestión académica') · {{ config('app.name') }}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="{{ asset('css/academic.css') }}" rel="stylesheet">
</head><body>
@auth
@php
$roles = ['admin' => 'Administrador', 'teacher' => 'Profesor', 'student' => 'Estudiante', 'guardian' => 'Encargado'];
$groups = ['Comunidad' => ['students', 'teachers', 'guardians', 'student_guardians'], 'Gestión académica' => ['enrollments', 'assignments'], 'Configuración' => ['school_years', 'grades', 'sections', 'subjects', 'periods', 'relationships', 'users']];
@endphp
<div class="app-shell">
<aside class="sidebar">
<a class="brand" href="{{ route('dashboard') }}"><img class="school-logo" src="{{ asset('images/colegio-logo.png') }}" alt="Escudo de {{ config('app.name') }}" width="44" height="44"><span>La Generación<small>del Mañana</small></span></a>
<details class="navigation" open><summary class="mobile-nav">Menú principal</summary>
<nav aria-label="Navegación principal">
<a href="{{ route('dashboard') }}" @class(['active' => request()->routeIs('dashboard')]) @if(request()->routeIs('dashboard')) aria-current="page" @endif>Resumen</a>
@if(auth()->user()->role === 'admin')
@foreach($groups as $label => $keys)
@if($label === 'Configuración')<details @if(in_array(request()->route('resource'), $keys)) open @endif><summary>Configuración</summary>@else<div class="nav-label">{{ $label }}</div>@endif
@foreach($keys as $key)
<a href="{{ route('resource.index', $key) }}" @class(['active' => request()->route('resource') === $key]) @if(request()->route('resource') === $key) aria-current="page" @endif>{{ \App\Http\Controllers\AcademicController::RESOURCES[$key][0] }}</a>
@endforeach
@if($label === 'Gestión académica')
<a href="{{ route('marks.index') }}" @class(['active' => request()->routeIs('marks.*')])>Calificaciones</a>
<a href="{{ route('report', 'enrollments') }}" @class(['active' => request()->routeIs('report')])>Informes</a>
@endif
@if($label === 'Configuración')</details>@endif
@endforeach
@elseif(auth()->user()->role === 'teacher')<a href="{{ route('marks.index') }}" @class(['active' => request()->routeIs('marks.*')])>Mis grupos</a>
@else<a href="{{ route('personal') }}" @class(['active' => request()->routeIs('personal')])>Mi expediente</a>@endif
</nav></details>
<div class="account"><div class="account-name">{{ auth()->user()->name }}</div><small>{{ $roles[auth()->user()->role] ?? 'Cuenta institucional' }}</small>
<div class="account-links"><a href="{{ route('password.change') }}">Contraseña</a><form action="{{ route('logout') }}" method="post">@csrf<button type="submit">Salir ↗</button></form></div></div>
</aside>
<div class="workspace"><header class="topbar"><span>Gestión institucional</span><span>{{ now(config('academic.timezone'))->format('d / m / Y') }}</span></header>
<main class="workspace-content" id="contenido"><div class="page-heading"><div><div class="eyebrow">Espacio académico</div><h1>@yield('title', 'Resumen')</h1></div></div>
@include('partials.feedback')
@yield('content')
</main></div></div>
@else
<main class="auth-page"><div class="auth-wrap"><div class="auth-brand"><img class="school-logo" src="{{ asset('images/colegio-logo.png') }}" alt="Escudo de {{ config('app.name') }}" width="44" height="44"> {{ config('app.name') }}</div>
@include('partials.feedback')
@yield('content')
<div class="auth-footer">Plataforma de gestión institucional</div></div></main>
@endauth
<script>
const menu = document.querySelector('.navigation');
if (menu) {
    const compact = window.matchMedia('(max-width: 767px)');
    const adaptMenu = () => { menu.open = !compact.matches; };
    adaptMenu();
    compact.addEventListener('change', adaptMenu);
}
</script>
</body></html>
