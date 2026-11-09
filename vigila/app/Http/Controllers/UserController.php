<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Camera;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', ['users' => User::where('role', 'user')->withCount('cameras')->latest()->get()]);
    }

    public function create(): View
    {
        return view('users.create', ['client' => new User(), 'cameras' => Camera::orderBy('name')->get(), 'assigned' => []]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'], 'role' => 'user']);
        $user->cameras()->sync($data['cameras'] ?? []);
        $this->audit($request, 'user.created', "Usuario creado: {$user->email}");

        return redirect()->route('users.index')->with('success', 'La cuenta de usuario fue creada.');
    }

    public function edit(User $user): View
    {
        abort_unless($user->role === 'user', 404);
        return view('users.edit', ['client' => $user, 'cameras' => Camera::orderBy('name')->get(), 'assigned' => $user->cameras()->pluck('cameras.id')->all()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'user', 404);
        $data = $this->validated($request, $user);
        $user->name = $data['name'];
        $user->email = $data['email'];
        if (filled($data['password'] ?? null)) $user->password = $data['password'];
        $user->save();
        $user->cameras()->sync($data['cameras'] ?? []);
        $this->audit($request, 'user.updated', "Usuario actualizado: {$user->email}");

        return redirect()->route('users.index')->with('success', 'La cuenta y sus accesos se actualizaron.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'user' && $user->id !== $request->user()->id, 404);
        $email = $user->email;
        $user->delete();
        $this->audit($request, 'user.deleted', "Usuario eliminado: {$email}");

        return redirect()->route('users.index')->with('success', 'La cuenta de usuario fue eliminada.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $emailRule = Rule::unique('users', 'email');
        if ($user) $emailRule->ignore($user->id);

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:10', 'confirmed'],
            'cameras' => ['nullable', 'array'], 'cameras.*' => ['integer', 'distinct', 'exists:cameras,id'],
        ]);
    }

    private function audit(Request $request, string $action, string $description): void
    {
        AuditLog::create(['user_id' => $request->user()->id, 'action' => $action, 'description' => $description, 'ip_address' => $request->ip()]);
    }
}
