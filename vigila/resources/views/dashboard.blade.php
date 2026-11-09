@extends('layouts.app')
@section('title', 'Resumen')
@section('crumb', 'Resumen')
@section('content')
<div data-live-refresh="admin-dashboard" data-refresh-ms="15000">
<div class="page-heading"><div><div class="eyebrow">MIÉRCOLES · OPERACIONES</div><h1>Resumen general</h1><p>Estado de tu infraestructura de videovigilancia en un solo lugar.</p></div><a class="button button-primary" href="{{ route('recordings.create') }}"><span>＋</span> Registrar grabación</a></div>
<div class="demo-banner"><span class="banner-icon">i</span><span><strong>Modo de demostración:</strong> métricas y costos calculados con los registros de esta aplicación. No son datos en vivo de AWS.</span></div>

<div class="metric-grid">
    <article class="metric-card"><div class="metric-head"><span>Cámaras registradas</span><span class="metric-icon purple">▣</span></div><div class="metric-value">{{ $cameraCount }}</div><div class="metric-foot"><span class="inline-dot green"></span>{{ $activeCameras }} activas <span class="muted">· inventario</span></div></article>
    <article class="metric-card"><div class="metric-head"><span>Grabaciones</span><span class="metric-icon blue">▤</span></div><div class="metric-value">{{ number_format($recordingCount) }}</div><div class="metric-foot"><span class="inline-dot blue-dot"></span>en el catálogo <span class="muted">· video</span></div></article>
    <article class="metric-card"><div class="metric-head"><span>Almacenamiento</span><span class="metric-icon orange">▱</span></div><div class="metric-value">{{ number_format($storedBytes / 1073741824, 1) }} <small>GB</small></div><div class="metric-foot"><span class="inline-dot orange-dot"></span>{{ $videoDisk === 's3' ? 'Amazon S3' : 'Disco local privado' }} <span class="muted">· {{ $s3Configured ? 'conectado' : 'demo' }}</span></div></article>
    <article class="metric-card"><div class="metric-head"><span>Costo mensual estimado</span><span class="metric-icon green">＄</span></div><div class="metric-value">${{ number_format($costs['total'], 2) }} <small>USD</small></div><div class="metric-foot"><span class="inline-dot green"></span>estimación de referencia <span class="muted">· AWS</span></div></article>
</div>

<div class="dashboard-grid">
    <section class="panel"><div class="panel-heading"><div><h2>Resumen de infraestructura</h2><p>Distribución estimada del gasto mensual</p></div><a href="{{ route('costs') }}" class="text-link">Ver costos <span>→</span></a></div>
        <div class="cost-summary"><div class="cost-total"><span>Estimado mensual</span><strong>${{ number_format($costs['total'], 2) }} <small>USD</small></strong><span class="muted">Valores de referencia configurables</span></div><div class="cost-lines"><div><span><i class="legend-dot dot-purple"></i>Cómputo EC2</span><strong>${{ number_format($costs['compute'], 2) }}</strong></div><div><span><i class="legend-dot dot-blue"></i>Almacenamiento S3</span><strong>${{ number_format($costs['storage'], 2) }}</strong></div><div><span><i class="legend-dot dot-orange"></i>Base de datos</span><strong>${{ number_format($costs['database'], 2) }}</strong></div></div></div>
        <div class="chart-bars" aria-label="Actividad semanal"><div class="chart-y"><span>100%</span><span>50%</span><span>0%</span></div><div class="bar-group"><i style="height:42%"></i><i style="height:55%"></i><i style="height:47%"></i><i style="height:76%"></i><i style="height:62%"></i><i style="height:88%"></i><i style="height:69%"></i><span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span></div></div>
    </section>
    <section class="panel"><div class="panel-heading"><div><h2>Estado de cámaras</h2><p>Disponibilidad del inventario</p></div><a href="{{ route('cameras.index') }}" class="text-link">Ver cámaras <span>→</span></a></div>
        @php($maintenance = $cameraCount - $activeCameras)
        <div class="camera-state"><div class="donut" style="--progress: {{ $cameraCount ? round($activeCameras / $cameraCount * 100) : 0 }}%"><div><strong>{{ $cameraCount ? round($activeCameras / $cameraCount * 100) : 0 }}%</strong><span>operativas</span></div></div><div class="state-legend"><div><span><i class="legend-dot dot-green"></i>Activas</span><strong>{{ $activeCameras }}</strong></div><div><span><i class="legend-dot dot-orange"></i>Mantenimiento / fuera de línea</span><strong>{{ $maintenance }}</strong></div></div></div>
        <div class="panel-divider"></div><div class="mini-note"><span class="inline-dot green"></span> Inventario actualizado <span class="muted">· {{ now()->format('H:i') }}</span></div>
    </section>
</div>

<section class="panel table-panel"><div class="panel-heading"><div><h2>Grabaciones recientes</h2><p>Últimos registros de video en la plataforma</p></div><a href="{{ route('recordings.index') }}" class="text-link">Ver todas <span>→</span></a></div>
    @if($recentRecordings->isEmpty())<div class="empty-state"><span>▤</span><h3>Aún no hay grabaciones</h3><p>Registra la primera para comenzar a organizar tus videos.</p><a class="button button-secondary" href="{{ route('recordings.create') }}">Registrar grabación</a></div>@else
    <div class="table-wrap"><table><thead><tr><th>GRABACIÓN</th><th>CÁMARA</th><th>FECHA Y HORA</th><th>DURACIÓN</th><th>TAMAÑO</th><th>ESTADO</th></tr></thead><tbody>
    @foreach($recentRecordings as $recording)<tr><td><span class="file-icon">▤</span><span class="table-primary">{{ $recording->title }}</span></td><td>{{ $recording->camera?->name ?? '—' }}</td><td>{{ $recording->recorded_at->format('d/m/Y · H:i') }}</td><td>{{ gmdate('i:s', $recording->duration_seconds) }}</td><td>{{ $recording->size_bytes ? number_format($recording->size_bytes / 1048576, 0).' MB' : '—' }}</td><td><span class="badge badge-green"><i></i>Lista</span></td></tr>@endforeach
    </tbody></table></div>@endif
</section>
<div class="live-updated">Actualización automática cada 15 segundos</div>
</div>
@endsection
