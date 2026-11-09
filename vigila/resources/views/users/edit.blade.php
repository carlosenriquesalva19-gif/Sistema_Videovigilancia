@extends('layouts.app')
@section('title', 'Editar usuario')
@section('crumb')<a href="{{ route('users.index') }}">Usuarios</a> <span>/</span> Editar@endsection
@section('content')
<div class="page-heading"><div><div class="eyebrow">ACCESOS DE CLIENTES</div><h1>Editar usuario</h1><p>Actualiza los datos y el acceso de {{ $client->name }}.</p></div></div>
<section class="panel form-panel"><form method="POST" action="{{ route('users.update', $client) }}">@csrf @method('PUT') @include('users.form')</form></section>
@endsection
