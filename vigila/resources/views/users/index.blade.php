@extends('layouts.app')
@section('title', 'Usuarios')
@section('crumb', 'Usuarios')
@section('content')
<div class="page-heading"><div><div class="eyebrow">ACCESOS DE CLIENTES</div><h1>Usuarios</h1><p>Crea cuentas y limita su acceso a cámaras específicas.</p></div><a href="{{ route('users.create') }}" class="button button-primary"><span>＋</span> Crear usuario</a></div>
<div class="demo-banner"><span class="banner-icon">i</span><span>Los usuarios ingresan a un portal separado. Solo ven las cámaras y grabaciones que les asignes; no acceden a costos, configuración ni administración.</span></div>
<section class="panel table-panel" data-live-refresh="admin-users" data-refresh-ms="15000"><div class="panel-heading"><div><h2>Cuentas de usuario</h2><p>{{ $users->count() }} cuentas de usuario</p></div></div>
    @if($users->isEmpty())<div class="empty-state"><span>♙</span><h3>Aún no hay usuarios</h3><p>Crea una cuenta y asígnale las cámaras que podrá consultar.</p><a href="{{ route('users.create') }}" class="button button-primary">Crear primer usuario</a></div>@else
    <div class="table-wrap"><table><thead><tr><th>USUARIO</th><th>CORREO</th><th>CÁMARAS ASIGNADAS</th><th>CREADO</th><th></th></tr></thead><tbody>@foreach($users as $client)<tr>
        <td><span class="camera-avatar">{{ strtoupper(substr($client->name, 0, 1)) }}</span><span class="table-primary">{{ $client->name }}</span></td><td>{{ $client->email }}</td><td><span class="badge badge-blue">{{ $client->cameras_count }} cámaras</span></td><td>{{ $client->created_at->format('d/m/Y') }}</td>
        <td><div class="row-actions"><a href="{{ route('users.edit', $client) }}" class="button button-small button-quiet">Editar accesos</a><form method="POST" action="{{ route('users.destroy', $client) }}" data-confirm="¿Eliminar esta cuenta? Se revocará su acceso al portal.">@csrf @method('DELETE')<button class="button button-small button-danger-quiet" type="submit">Eliminar</button></form></div></td>
    </tr>@endforeach</tbody></table></div>@endif
</section>
@endsection
