<!doctype html>
<html lang="es"><head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar · VIGILA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="login-page"><div class="login-shell">
    <div class="login-brand"><span class="brand-mark">V</span><span>VIGILA<small>Cloud Security</small></span></div>
    <div class="login-card"><div class="eyebrow">CENTRO DE CONTROL</div><h1>Bienvenido de vuelta</h1><p>Ingresa para supervisar tu infraestructura.</p>
        @if($errors->any())<div class="flash flash-error"><div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div></div>@endif
        <form method="POST" action="{{ route('login.store') }}" class="form-stack">@csrf
            <label>Correo electrónico<input type="email" name="email" value="{{ old('email') }}" placeholder="nombre@empresa.com" required autofocus autocomplete="username"></label>
            <label>Contraseña<input type="password" name="password" placeholder="••••••••••" required autocomplete="current-password"></label>
            <label class="checkbox-label"><input type="checkbox" name="remember" value="1"> Mantener sesión iniciada</label>
            <button class="button button-primary button-wide" type="submit">Ingresar al panel <span>→</span></button>
        </form>
        <div class="login-foot"><span class="lock-icon">◇</span> Conexión protegida · Sesión cifrada</div>
    </div>
    <p class="login-caption">VIGILA · Plataforma de videovigilancia en la nube</p>
</div></body></html>
