@extends('layouts.app')
@section('title', 'Seguridad')
@section('crumb', 'Seguridad')
@section('content')
<div class="page-heading"><div><div class="eyebrow">PROTECCIÓN Y AUDITORÍA</div><h1>Seguridad</h1><p>Controles de acceso de la aplicación y recomendaciones para la cuenta AWS.</p></div></div>
<div class="demo-banner"><span class="banner-icon">i</span><span>El panel verifica controles propios de la aplicación. La postura de una cuenta AWS requiere integrar servicios como CloudTrail, Config, GuardDuty y IAM Access Analyzer.</span></div>
<div class="security-grid">
    <section class="panel"><div class="panel-heading"><div><h2>Controles de la aplicación</h2><p>Activos en esta versión</p></div><span class="badge badge-blue"><i></i>Aplicación</span></div>
        <div class="control-list">
            <div class="control-row"><span class="control-check">✓</span><div><strong>Autenticación y sesión</strong><small>Acceso requerido al panel · sesión cifrada y regenerada al iniciar</small></div><span class="control-state">Activo</span></div>
            <div class="control-row"><span class="control-check">✓</span><div><strong>Protección CSRF</strong><small>Formularios validados con token de sesión</small></div><span class="control-state">Activo</span></div>
            <div class="control-row"><span class="control-check">✓</span><div><strong>Limitación de intentos</strong><small>Control de inicio de sesión por usuario y dirección IP</small></div><span class="control-state">Activo</span></div>
            <div class="control-row"><span class="control-check">✓</span><div><strong>Bitácora de actividad</strong><small>Inicio de sesión, cambios y acceso a grabaciones</small></div><span class="control-state">Activo</span></div>
            <div class="control-row"><span class="control-check">✓</span><div><strong>Archivos de video privados</strong><small>Acceso temporal firmado cuando lo permite el disco configurado</small></div><span class="control-state">{{ $videoDisk === 's3' ? 'S3 configurado' : 'Modo local' }}</span></div>
        </div>
    </section>
    <section class="panel"><div class="panel-heading"><div><h2>Lista de verificación AWS</h2><p>Aplicar y verificar en la cuenta de destino</p></div></div>
        <div class="control-list">
            <div class="control-row"><span class="control-empty">□</span><div><strong>IAM de mínimo privilegio y MFA</strong><small>Roles de aplicación, MFA de administradores y credenciales temporales</small></div><span class="control-state pending">Por verificar</span></div>
            <div class="control-row"><span class="control-empty">□</span><div><strong>VPC y grupos de seguridad</strong><small>RDS privado; acceso solo desde grupos autorizados</small></div><span class="control-state pending">Por verificar</span></div>
            <div class="control-row"><span class="control-empty">□</span><div><strong>Cifrado con KMS</strong><small>S3, RDS, EBS, snapshots y respaldos</small></div><span class="control-state pending">Por verificar</span></div>
            <div class="control-row"><span class="control-empty">□</span><div><strong>CloudTrail, Config y GuardDuty</strong><small>Auditoría, detección y notificaciones de cambios</small></div><span class="control-state pending">Por verificar</span></div>
            <div class="control-row"><span class="control-empty">□</span><div><strong>S3 Block Public Access</strong><small>Bloqueo público y política de acceso privado al bucket</small></div><span class="control-state pending">Por verificar</span></div>
        </div>
    </section>
</div>
<section class="panel table-panel" data-live-refresh="audit-events" data-refresh-ms="15000"><div class="panel-heading"><div><h2>Actividad reciente</h2><p>Eventos registrados por la aplicación</p></div></div>
    @if($events->isEmpty())<div class="empty-state compact"><p>Aún no hay eventos en la bitácora.</p></div>@else<div class="table-wrap"><table><thead><tr><th>EVENTO</th><th>USUARIO</th><th>DIRECCIÓN IP</th><th>FECHA</th></tr></thead><tbody>@foreach($events as $event)<tr><td><span class="table-primary">{{ $event->description }}</span><small class="table-sub">{{ $event->action }}</small></td><td>{{ $event->user?->name ?? 'Usuario eliminado' }}</td><td>{{ $event->ip_address ?? '—' }}</td><td>{{ $event->created_at->format('d/m/Y · H:i:s') }}</td></tr>@endforeach</tbody></table></div>@endif
</section>
@endsection
