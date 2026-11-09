<!doctype html>
<html lang="es"><head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mis cámaras') · VIGILA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/live.css') }}">
</head>
<body><div class="app-shell">
    <aside class="sidebar"><a href="{{ route('portal') }}" class="brand"><span class="brand-mark">V</span><span>VIGILA<small>Cloud Security</small></span></a><div class="workspace-label">PORTAL DE USUARIO</div><nav class="nav-list"><a class="nav-item {{ request()->routeIs('portal') ? 'active' : '' }}" href="{{ route('portal') }}"><span class="nav-icon">◫</span>Mis cámaras</a></nav>
        <div class="sidebar-bottom"><div class="cloud-card"><div class="cloud-card-top"><span class="status-dot"></span> Acceso protegido</div><p>Tu cuenta solo puede consultar los dispositivos autorizados por VIGILA.</p></div><div class="user-card"><span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><span class="user-copy"><strong>{{ auth()->user()->name }}</strong><small>Usuario</small></span><form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-button" title="Cerrar sesión" aria-label="Cerrar sesión">↗</button></form></div></div>
    </aside>
    <main class="main-area"><header class="topbar"><div class="breadcrumb">VIGILA <span>/</span> @yield('crumb', 'Mis cámaras')</div><div class="topbar-right"><span class="environment-pill"><i></i> Portal seguro</span><span class="top-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span></div></header><section class="page-content">@if(session('success'))<div class="flash flash-success">✓ {{ session('success') }}</div>@endif @yield('content')</section></main>
</div><script src="{{ asset('js/app.js') }}" defer></script></body></html>
