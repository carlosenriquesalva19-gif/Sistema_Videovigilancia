<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') · VIGILA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"><link rel="stylesheet" href="{{ asset('css/portal.css') }}"><link rel="stylesheet" href="{{ asset('css/live.css') }}">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('dashboard') }}" class="brand"><span class="brand-mark">V</span><span>VIGILA<small>Cloud Security</small></span></a>
        <div class="workspace-label">ESPACIO DE TRABAJO</div>
        <nav class="nav-list">
            <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-icon">◫</span>Resumen</a>
            <a class="nav-item {{ request()->routeIs('cameras.*') ? 'active' : '' }}" href="{{ route('cameras.index') }}"><span class="nav-icon">▣</span>Cámaras</a>
            <a class="nav-item {{ request()->routeIs('recordings.*') ? 'active' : '' }}" href="{{ route('recordings.index') }}"><span class="nav-icon">▤</span>Grabaciones</a>
            <a class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><span class="nav-icon">👥</span>Usuarios</a>
            <a class="nav-item {{ request()->routeIs('costs') ? 'active' : '' }}" href="{{ route('costs') }}"><span class="nav-icon">◒</span>Costos cloud</a>
            <a class="nav-item {{ request()->routeIs('security') ? 'active' : '' }}" href="{{ route('security') }}"><span class="nav-icon">◇</span>Seguridad</a>
            <a class="nav-item {{ request()->routeIs('operations') ? 'active' : '' }}" href="{{ route('operations') }}"><span class="nav-icon">⟳</span>Operaciones</a>
        </nav>
        <div class="sidebar-bottom">
            <div class="cloud-card"><div class="cloud-card-top"><span class="status-dot"></span> Servicio operativo</div><p>Almacenamiento</p><div class="cloud-progress"><span style="width: 38%"></span></div><small>Uso registrado en el sistema</small></div>
            <div class="user-card"><span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><span class="user-copy"><strong>{{ auth()->user()->name }}</strong><small>Administrador</small></span><form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-button" title="Cerrar sesión" aria-label="Cerrar sesión">↗</button></form></div>
        </div>
    </aside>

    <main class="main-area">
        <header class="topbar"><button class="mobile-menu" id="menu-toggle" aria-label="Abrir menú">☰</button><div class="breadcrumb">VIGILA <span>/</span> @yield('crumb', 'Resumen')</div><div class="topbar-right"><span class="environment-pill"><i></i> Producción</span><span class="top-date">{{ now()->translatedFormat('D, d M Y') }}</span><span class="top-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span></div></header>
        <section class="page-content">
            @if(session('success'))<div class="flash flash-success"><span>✓</span>{{ session('success') }}<button type="button" onclick="this.parentElement.remove()">×</button></div>@endif
            @if($errors->any())<div class="flash flash-error"><span>!</span><div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div><button type="button" onclick="this.parentElement.remove()">×</button></div>@endif
            @yield('content')
        </section>
    </main>
</div>
<script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
