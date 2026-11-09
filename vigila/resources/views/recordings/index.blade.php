@extends('layouts.app')
@section('title', 'Grabaciones')
@section('crumb', 'Grabaciones')
@section('content')
<div class="page-heading"><div><div class="eyebrow">ARCHIVO DE VIDEO</div><h1>Grabaciones</h1><p>Busca y administra los archivos registrados en la plataforma.</p></div><a href="{{ route('recordings.create') }}" class="button button-primary"><span>＋</span> Registrar grabación</a></div>
<section class="panel table-panel">
    <form method="GET" class="filter-bar"><label>Cámara<select name="camera_id"><option value="">Todas las cámaras</option>@foreach($cameras as $camera)<option value="{{ $camera->id }}" @selected(request('camera_id') == $camera->id)>{{ $camera->name }}</option>@endforeach</select></label><label>Desde<input type="date" name="from" value="{{ request('from') }}"></label><label>Hasta<input type="date" name="to" value="{{ request('to') }}"></label><button class="button button-secondary" type="submit">Filtrar</button><a class="button button-quiet" href="{{ route('recordings.index') }}">Limpiar</a></form>
    <div data-live-refresh="admin-recording-results" data-refresh-ms="15000">@if($recordings->isEmpty())<div class="empty-state"><span>▤</span><h3>No hay grabaciones</h3><p>Ajusta los filtros o registra una nueva grabación.</p><a href="{{ route('recordings.create') }}" class="button button-primary">Registrar grabación</a></div>@else
    <div class="table-wrap"><table><thead><tr><th>GRABACIÓN</th><th>CÁMARA</th><th>FECHA Y HORA</th><th>DURACIÓN</th><th>TAMAÑO</th><th>ALMACENAMIENTO</th><th></th></tr></thead><tbody>@foreach($recordings as $recording)<tr>
        <td><span class="file-icon">▤</span><span class="table-primary">{{ $recording->title }}</span></td><td>{{ $recording->camera?->name ?? '—' }}</td><td>{{ $recording->recorded_at->format('d/m/Y · H:i') }}</td><td>{{ gmdate('i:s', $recording->duration_seconds) }}</td><td>{{ $recording->size_bytes ? number_format($recording->size_bytes / 1048576, 0).' MB' : 'Sin archivo' }}</td><td><span class="storage-tag">{{ $recording->storage_class }}</span></td>
        <td><div class="row-actions">@if($recording->file_path)<a class="button button-small button-quiet" href="{{ route('recordings.download', $recording) }}">Abrir</a>@endif<form method="POST" action="{{ route('recordings.destroy', $recording) }}" data-confirm="¿Eliminar esta grabación y su archivo asociado?">@csrf @method('DELETE')<button class="button button-small button-danger-quiet" type="submit">Eliminar</button></form></div></td>
    </tr>@endforeach</tbody></table></div><div class="pagination-wrap">{{ $recordings->links() }}</div>@endif</div>
</section>
<div class="info-strip"><span class="info-icon">i</span><span>La carga se guarda de forma privada en el disco configurado: <strong>{{ config('vigila.video_disk') === 's3' ? 'Amazon S3' : 'almacenamiento local' }}</strong>. Los enlaces de acceso expiran a los 5 minutos cuando el proveedor lo permite.</span></div>
@endsection
