@extends('layouts.app')
@section('title', 'Agregar cámara')
@section('crumb')<a href="{{ route('cameras.index') }}">Cámaras</a> <span>/</span> Nueva cámara@endsection
@section('content')
<div class="page-heading"><div><div class="eyebrow">INVENTARIO DE DISPOSITIVOS</div><h1>Agregar cámara</h1><p>Registra el dispositivo y define su política de retención.</p></div></div>
<section class="panel form-panel"><form method="POST" action="{{ route('cameras.store') }}">@csrf @include('cameras.form')</form></section>
@endsection
