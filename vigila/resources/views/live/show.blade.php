@extends('layouts.app')
@section('title', 'Vista en vivo')
@section('crumb')<a href="{{ route('cameras.index') }}">Cámaras</a> <span>/</span> Vista en vivo@endsection
@section('content')
<div class="page-heading"><div><div class="eyebrow">MONITOREO · {{ $camera->location }}</div><h1>{{ $camera->name }}</h1><p>Transmisión en vivo de la cámara.</p></div><a href="{{ route('cameras.index') }}" class="button button-secondary">← Volver a cámaras</a></div>
<div class="live-player-card" data-live-player data-session-url="{{ $sessionEndpoint }}"><div class="live-player-top"><span class="live-label"><i></i> EN VIVO</span><span class="live-connection" data-live-status>Conectando...</span></div><video class="live-video" controls autoplay muted playsinline hidden></video><img class="live-mjpeg" alt="Transmisión en vivo" hidden><div class="live-placeholder"><span>◉</span><strong>Conectando con la cámara</strong><small>La transmisión puede tardar unos segundos en iniciar.</small><button class="button button-small button-secondary" data-live-retry type="button">Reintentar</button></div><div class="live-player-bottom"><span>{{ $camera->aws_stream_name ? 'Amazon Kinesis Video Streams · HLS' : 'Fuente directa de cámara' }}</span><span>Acceso protegido</span></div></div>
<div class="info-strip"><span class="info-icon">i</span><span>Si la conexión falla, confirma que la cámara esté enviando video, que el nombre del stream y la región coincidan y que el rol IAM del servidor pueda leer Kinesis Video Streams. Las URL RTSP requieren un gateway/proxy compatible; el navegador no reproduce RTSP directamente.</span></div>
<script src="https://cdn.jsdelivr.net/npm/hls.js@1.7.3"></script><script src="{{ asset('js/live.js') }}" defer></script>
@endsection
