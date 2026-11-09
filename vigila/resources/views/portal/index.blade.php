@extends('portal.layout')
@section('title', 'Mis cámaras')
@section('content')
<div data-live-refresh="user-portal" data-refresh-ms="15000">
<div class="page-heading"><div><div class="eyebrow">PORTAL DE USUARIO</div><h1>Hola, {{ auth()->user()->name }}</h1><p>Consulta las cámaras y grabaciones que tienes autorizadas.</p></div></div>
<div class="user-welcome"><div><span class="welcome-icon">▣</span><div><strong>{{ $cameras->count() }} cámaras asignadas</strong><p>El administrador de VIGILA controla qué dispositivos aparecen aquí.</p></div></div><span class="welcome-shield">◇</span></div>
<div class="section-title"><div><h2>Mis cámaras</h2><p>Selecciona una cámara para ver sus grabaciones</p></div></div>
@if($cameras->isEmpty())<section class="panel"><div class="empty-state"><span>▣</span><h3>Todavía no tienes cámaras asignadas</h3><p>Solicita al administrador que te habilite el acceso a una o más cámaras.</p></div></section>@else
<div class="user-camera-grid">@foreach($cameras as $camera)<a href="{{ route('portal.cameras.show', $camera) }}" class="user-camera-card"><div class="user-camera-preview"><span class="preview-corner">VISTA PREVIA</span><span class="camera-big-icon">▣</span><span class="preview-status"><i></i>{{ $camera->status === 'active' ? 'Activa' : ($camera->status === 'maintenance' ? 'Mantenimiento' : 'Sin conexión') }}</span></div><div class="user-camera-info"><div><h3>{{ $camera->name }}</h3><p>⌖ {{ $camera->location }}</p></div><span class="camera-arrow">→</span><div class="user-camera-meta"><span>▤ {{ $camera->recordings_count }} grabaciones</span><span>Retención {{ $camera->retention_days }} días</span></div></div></a>@endforeach</div>
@endif
<section class="panel table-panel user-recordings"><div class="panel-heading"><div><h2>Grabaciones recientes</h2><p>Actividad de las cámaras asignadas</p></div></div>
    @if($recordings->isEmpty())<div class="empty-state compact"><p>No hay grabaciones disponibles.</p></div>@else<div class="table-wrap"><table><thead><tr><th>GRABACIÓN</th><th>CÁMARA</th><th>FECHA Y HORA</th><th>DURACIÓN</th><th>ARCHIVO</th></tr></thead><tbody>@foreach($recordings as $recording)<tr><td><span class="file-icon">▤</span><span class="table-primary">{{ $recording->title }}</span></td><td>{{ $recording->camera?->name }}</td><td>{{ $recording->recorded_at->format('d/m/Y · H:i') }}</td><td>{{ gmdate('i:s', $recording->duration_seconds) }}</td><td>@if($recording->file_path)<a class="button button-small button-quiet" href="{{ route('recordings.download', $recording) }}">Ver video</a>@else<span class="muted">Sin archivo</span>@endif</td></tr>@endforeach</tbody></table></div>@endif
</section>
<div class="live-updated">Actualización automática cada 15 segundos</div>
</div>
@endsection
