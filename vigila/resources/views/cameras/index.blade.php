@extends('layouts.app')
@section('title', 'Cámaras')
@section('crumb', 'Cámaras')
@section('content')
<div class="page-heading"><div><div class="eyebrow">INVENTARIO</div><h1>Cámaras</h1><p>Administra dispositivos y políticas de retención de video.</p></div><a href="{{ route('cameras.create') }}" class="button button-primary"><span>＋</span> Agregar cámara</a></div>
<section class="panel table-panel" data-live-refresh="admin-cameras" data-refresh-ms="15000"><div class="panel-heading"><div><h2>Dispositivos registrados</h2><p>{{ $cameras->count() }} cámaras en el inventario</p></div></div>
    @if($cameras->isEmpty())<div class="empty-state"><span>▣</span><h3>Sin cámaras registradas</h3><p>Agrega una cámara para empezar a administrar sus grabaciones.</p><a href="{{ route('cameras.create') }}" class="button button-primary">Agregar cámara</a></div>@else
    <div class="table-wrap"><table><thead><tr><th>CÁMARA</th><th>UBICACIÓN</th><th>GRABACIONES</th><th>RETENCIÓN</th><th>ESTADO</th><th></th></tr></thead><tbody>@foreach($cameras as $camera)<tr>
        <td><span class="camera-avatar">▣</span><span class="table-primary">{{ $camera->name }}</span></td><td>{{ $camera->location }}</td><td>{{ $camera->recordings_count }}</td><td>{{ $camera->retention_days }} días</td>
        <td>@if($camera->status === 'active')<span class="badge badge-green"><i></i>Activa</span>@elseif($camera->status === 'maintenance')<span class="badge badge-orange"><i></i>Mantenimiento</span>@else<span class="badge badge-gray"><i></i>Fuera de línea</span>@endif</td>
        <td><div class="row-actions">@if($camera->aws_stream_name || $camera->stream_url)<a class="button button-small button-quiet" href="{{ route('cameras.live', $camera) }}">Ver en vivo</a>@endif<a class="button button-small button-quiet" href="{{ route('cameras.edit', $camera) }}">Editar</a><form method="POST" action="{{ route('cameras.destroy', $camera) }}" data-confirm="¿Eliminar esta cámara y los registros asociados?">@csrf @method('DELETE')<button class="button button-small button-danger-quiet" type="submit">Eliminar</button></form></div></td>
    </tr>@endforeach</tbody></table></div>@endif
</section>
<div class="info-strip"><span class="info-icon">i</span><span>Las cámaras y grabaciones se administran desde esta aplicación. Para recibir una transmisión en vivo se requiere conectar el dispositivo y el servicio de ingesta correspondiente.</span></div>
@endsection
