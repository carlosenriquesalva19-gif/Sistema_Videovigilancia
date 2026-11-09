@extends('layouts.app')
@section('title', 'Crear usuario')
@section('crumb')<a href="{{ route('users.index') }}">Usuarios</a> <span>/</span> Nueva cuenta@endsection
@section('content')
<div class="page-heading"><div><div class="eyebrow">ACCESOS DE CLIENTES</div><h1>Crear usuario</h1><p>Registra una cuenta de consulta y elige las cámaras autorizadas.</p></div></div>
<section class="panel form-panel"><form method="POST" action="{{ route('users.store') }}">@csrf @include('users.form')</form></section>
@endsection
