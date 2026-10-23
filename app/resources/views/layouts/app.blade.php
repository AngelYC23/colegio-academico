<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Gestión académica') · Colegio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background:#f2f5f9;color:#172b46} .sidebar{background:#112942} .sidebar a{color:#dbe5ef;text-decoration:none;display:block;padding:.5rem .8rem;border-radius:.5rem} .sidebar a:hover,.sidebar a.active{background:#254c72;color:white} .brand{letter-spacing:.06em} .card{border:0;box-shadow:0 2px 14px #16375913} .table-responsive{border-radius:.5rem}
        @media(min-width:768px){.sidebar{position:sticky;top:0;align-self:flex-start;height:100vh;height:100dvh;overflow-y:auto}}
    </style>
</head>
<body>
@auth
<div class="container-fluid"><div class="row">
    <aside class="col-md-3 col-lg-2 sidebar p-3">
        <a class="brand fw-bold text-white fs-5 mb-3" href="{{ route('dashboard') }}">Colegio · Académico</a>
        <nav aria-label="Navegación principal"><a href="{{ route('dashboard') }}">Inicio</a>
        @if(auth()->user()->role === 'admin')
            @foreach(\App\Http\Controllers\AcademicController::RESOURCES as $key => $config)
                <a href="{{ route('resource.index', $key) }}">{{ $config[0] }}</a>
            @endforeach
            <a href="{{ route('marks.index') }}">Calificaciones</a>
            @foreach(['enrollments' => 'Inscritos', 'teachers' => 'Profesores', 'assignments' => 'Asignaciones', 'marks' => 'Notas'] as $key => $label)
                <a href="{{ route('report', $key) }}">Informe: {{ $label }}</a>
            @endforeach
        @elseif(auth()->user()->role === 'teacher')<a href="{{ route('marks.index') }}">Mis grupos y calificaciones</a>
        @else<a href="{{ route('personal') }}">Mi expediente</a>@endif
        <a href="{{ route('password.change') }}">Cambiar contraseña</a></nav>
        <form action="{{ route('logout') }}" method="post" class="mt-3">@csrf <button class="btn btn-outline-light w-100">Cerrar sesión</button></form>
    </aside>
    <main class="col-md-9 col-lg-10 px-3 px-lg-5 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">@yield('title', 'Panel académico')</h1><span class="small text-muted">{{ auth()->user()->name }} · {{ auth()->user()->role }}</span></div>
        @if(session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger" role="alert"><strong>Revise la información:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div></div>
@else
<main class="container py-5">@if(session('status'))<div class="alert alert-info">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')</main>
@endauth
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
