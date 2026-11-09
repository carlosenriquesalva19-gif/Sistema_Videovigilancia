<div class="form-grid">
    <label>Nombre completo <span class="required">*</span><input name="name" value="{{ old('name', $client->name) }}" placeholder="Nombre del usuario" required maxlength="120"></label>
    <label>Correo electrónico <span class="required">*</span><input type="email" name="email" value="{{ old('email', $client->email) }}" placeholder="usuario@empresa.com" required maxlength="255" autocomplete="off"></label>
    <label>Contraseña {{ $client->exists ? '' : '*' }}<input type="password" name="password" @required(!$client->exists) minlength="10" autocomplete="new-password" placeholder="{{ $client->exists ? 'Dejar vacío para conservar' : 'Mínimo 10 caracteres' }}"><small class="field-help">{{ $client->exists ? 'Déjala vacía si no deseas cambiarla.' : 'El usuario podrá cambiarla con el administrador.' }}</small></label>
    <label>Confirmar contraseña<input type="password" name="password_confirmation" @required(!$client->exists) minlength="10" autocomplete="new-password" placeholder="Repite la contraseña"></label>
</div>
<div class="assignment-section"><div class="assignment-heading"><div><strong>Cámaras visibles para este usuario</strong><small>Selecciona las cámaras a las que podrá acceder.</small></div><span class="badge badge-blue">{{ $cameras->count() }} disponibles</span></div>
    @if($cameras->isEmpty())<div class="assignment-empty">Primero registra cámaras en el inventario.</div>@else<div class="camera-check-grid">@foreach($cameras as $camera)
        <label class="camera-check"><input type="checkbox" name="cameras[]" value="{{ $camera->id }}" @checked(in_array($camera->id, old('cameras', $assigned)))><span class="camera-check-icon">▣</span><span><strong>{{ $camera->name }}</strong><small>{{ $camera->location }}</small></span></label>
    @endforeach</div>@endif
</div>
<div class="form-actions"><a href="{{ route('users.index') }}" class="button button-secondary">Cancelar</a><button class="button button-primary" type="submit">{{ $client->exists ? 'Guardar usuario' : 'Crear cuenta' }} <span>→</span></button></div>
