@extends('layouts.app')
@section('title', 'Operaciones')
@section('crumb', 'Operaciones')
@section('content')
<div class="page-heading"><div><div class="eyebrow">MANTENIMIENTO Y ADMINISTRACIÓN</div><h1>Operaciones</h1><p>Guía de automatización y mantenimiento para la infraestructura VIGILA.</p></div></div>
<div class="demo-banner"><span class="banner-icon">i</span><span>Systems Manager todavía no está conectado a una cuenta AWS desde este entorno. Esta sección documenta las tareas a activar en la cuenta del proyecto.</span></div>
<div class="ops-grid">
    <article class="ops-card"><div class="ops-icon purple">⌕</div><span class="ops-label">INVENTARIO</span><h2>Systems Manager Inventory</h2><p>Recopila sistema operativo, aplicaciones, parches y configuración de las instancias administradas.</p><div class="ops-status"><span class="inline-dot orange-dot"></span> Pendiente de conexión AWS</div></article>
    <article class="ops-card"><div class="ops-icon blue">↻</div><span class="ops-label">ACTUALIZACIONES</span><h2>Patch Manager</h2><p>Define líneas base de parches y ventanas de mantenimiento. Despliega primero en pruebas y luego por lotes en producción.</p><div class="ops-status"><span class="inline-dot orange-dot"></span> Pendiente de conexión AWS</div></article>
    <article class="ops-card"><div class="ops-icon green">⌘</div><span class="ops-label">ACCESO SEGURO</span><h2>Session Manager</h2><p>Administra instancias sin exponer SSH/RDP a Internet ni repartir llaves privadas. Registra las sesiones administrativas.</p><div class="ops-status"><span class="inline-dot orange-dot"></span> Pendiente de conexión AWS</div></article>
    <article class="ops-card"><div class="ops-icon orange">⚙</div><span class="ops-label">AUTOMATIZACIÓN</span><h2>Runbooks de operación</h2><p>Estandariza tareas de reinicio, verificación, recuperación y despliegue con permisos limitados y registro de cambios.</p><div class="ops-status"><span class="inline-dot orange-dot"></span> Pendiente de conexión AWS</div></article>
</div>
<section class="panel"><div class="panel-heading"><div><h2>Secuencia de mantenimiento sugerida</h2><p>Flujo para cambios controlados</p></div></div><div class="timeline">
    <div class="timeline-item"><span>1</span><div><strong>Revisar cumplimiento</strong><p>Consultar inventario, parches pendientes, alarmas y ventanas de respaldo.</p></div></div>
    <div class="timeline-item"><span>2</span><div><strong>Aplicar en entorno de prueba</strong><p>Ejecutar el runbook con la instancia canary y verificar el servicio y los registros.</p></div></div>
    <div class="timeline-item"><span>3</span><div><strong>Desplegar progresivamente</strong><p>Actualizar grupos pequeños, observar métricas y pausar automáticamente ante errores.</p></div></div>
    <div class="timeline-item"><span>4</span><div><strong>Confirmar y auditar</strong><p>Registrar el cambio, confirmar parches y probar restauración y recuperación.</p></div></div>
</div></section>
@endsection
