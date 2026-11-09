@extends('layouts.app')
@section('title', 'Editar cámara')
@section('crumb')<a href="{{ route('cameras.index') }}">Cámaras</a> <span>/</span> Editar@endsection
@section('content')
<div class="page-heading"><div><div class="eyebrow">INVENTARIO DE DISPOSITIVOS</div><h1>Editar cámara</h1><p>Actualiza la información de {{ $camera->name }}.</p></div></div>
<section class="panel form-panel"><form method="POST" action="{{ route('cameras.update', $camera) }}">@csrf @method('PUT') @include('cameras.form')</form></section>
@endsection
