@extends('layouts.app')
@section('title', 'Registrar grabación')
@section('crumb')<a href="{{ route('recordings.index') }}">Grabaciones</a> <span>/</span> Registrar@endsection
@section('content')
<div class="page-heading"><div><div class="eyebrow">ARCHIVO DE VIDEO</div><h1>Registrar grabación</h1><p>Asocia un archivo de video con una cámara y su fecha de captura.</p></div></div>
<section class="panel form-panel"><form method="POST" action="{{ route('recordings.store') }}" enctype="multipart/form-data">@csrf
    <div class="form-grid">
        <label class="span-2">Nombre de la grabación <span class="required">*</span><input name="title" value="{{ old('title') }}" placeholder="Ej. Movimiento en el acceso principal" required maxlength="180"></label>
        <label>Cámara <span class="required">*</span><select name="camera_id" required><option value="">Seleccionar cámara</option>@foreach($cameras as $camera)<option value="{{ $camera->id }}" @selected(old('camera_id') == $camera->id)>{{ $camera->name }} · {{ $camera->location }}</option>@endforeach</select></label>
        <label>Fecha y hora de captura <span class="required">*</span><input type="datetime-local" name="recorded_at" value="{{ old('recorded_at', now()->format('Y-m-d\TH:i')) }}" required></label>
        <label>Duración (segundos) <span class="required">*</span><input type="number" name="duration_seconds" value="{{ old('duration_seconds', 60) }}" min="1" max="86400" required></label>
        <label class="span-2">Archivo de video <span class="optional">Opcional · máximo 500 MB</span><input type="file" name="video" accept="video/mp4,video/quicktime,video/webm,video/x-matroska"><small class="field-help">Formatos aceptados: MP4, MOV, WebM y MKV. El archivo se guarda como privado.</small></label>
        @if(app()->environment('local'))<div class="span-2 upload-diagnostics">Límites activos de este servidor PHP: <code>upload_max_filesize={{ ini_get('upload_max_filesize') }}</code> · <code>post_max_size={{ ini_get('post_max_size') }}</code> · <code>file_uploads={{ ini_get('file_uploads') }}</code></div>@endif
    </div>
    <div class="form-actions"><a href="{{ route('recordings.index') }}" class="button button-secondary">Cancelar</a><button class="button button-primary" type="submit">Guardar grabación <span>→</span></button></div>
</form></section>
@endsection
