@extends('layouts.app')
@section('title', 'Costos cloud')
@section('crumb', 'Costos cloud')
@section('content')
<div data-live-refresh="cost-estimate" data-refresh-ms="30000">
@php($total = $compute + $storage + $database)
<div class="page-heading"><div><div class="eyebrow">FINOPS · AWS</div><h1>Costos cloud</h1><p>Estimación basada en la actividad registrada en el sistema.</p></div></div>
<div class="demo-banner"><span class="banner-icon">i</span><span><strong>Estimación educativa, no facturación real.</strong> Para consultar costos exactos hay que integrar AWS Cost Explorer o Cost and Usage Reports y configurar región, uso y precios vigentes.</span></div>
<div class="cost-hero"><div><span class="eyebrow">PROYECCIÓN MENSUAL</span><div class="hero-amount">${{ number_format($total, 2) }} <small>USD / mes</small></div><p>Los valores unitarios se ajustan en el archivo <code>.env</code>.</p></div><div class="hero-symbol">＄</div></div>
<div class="cost-card-grid">
    <article class="cost-card"><span class="metric-icon purple">▣</span><div class="cost-label">Cómputo · EC2</div><strong>${{ number_format($compute, 2) }}</strong><p>{{ config('vigila.estimated_ec2_instances') }} instancias de referencia × 720 h × tarifa EC2.</p><div class="cost-card-foot"><span class="legend-dot dot-purple"></span>VIGILA_ESTIMATED_EC2_INSTANCES</div></article>
    <article class="cost-card"><span class="metric-icon blue">▱</span><div class="cost-label">Almacenamiento · S3</div><strong>${{ number_format($storage, 2) }}</strong><p>{{ number_format($storedBytes / 1073741824, 2) }} GB registrados × tarifa por GB-mes de referencia.</p><div class="cost-card-foot"><span class="legend-dot dot-blue"></span>VIGILA_RATE_S3_GB_MONTH</div></article>
    <article class="cost-card"><span class="metric-icon orange">◈</span><div class="cost-label">Base de datos · RDS</div><strong>${{ number_format($database, 2) }}</strong><p>720 horas × tarifa de referencia por hora de base de datos.</p><div class="cost-card-foot"><span class="legend-dot dot-orange"></span>VIGILA_RATE_DB_HOUR</div></article>
</div>
<section class="panel"><div class="panel-heading"><div><h2>Acciones para reducir costos</h2><p>Aplicables al diseño de infraestructura descrito en la propuesta.</p></div></div><div class="recommendation-list">
    <div class="recommendation"><span class="recommendation-number">01</span><div><strong>Escalado automático de EC2</strong><p>Ajusta capacidad a la carga real; programa reducción fuera de horarios de baja demanda.</p></div><span class="impact-pill">Compute</span></div>
    <div class="recommendation"><span class="recommendation-number">02</span><div><strong>Spot para procesamiento tolerante a fallos</strong><p>Usa EC2 Spot en workers recuperables, con colas, reintentos y capacidad On-Demand de respaldo.</p></div><span class="impact-pill">Compute</span></div>
    <div class="recommendation"><span class="recommendation-number">03</span><div><strong>Políticas de ciclo de vida en S3</strong><p>Transiciona videos antiguos a clases de archivo y elimina objetos vencidos según retención aprobada.</p></div><span class="impact-pill">Storage</span></div>
    <div class="recommendation"><span class="recommendation-number">04</span><div><strong>Dimensionamiento correcto de RDS</strong><p>Revisa métricas y consultas antes de escalar; deriva lecturas a réplicas si el patrón lo permite.</p></div><span class="impact-pill">Database</span></div>
</div></section>
<div class="live-updated">Estimación actualizada automáticamente cada 30 segundos.</div>
</div>
@endsection
