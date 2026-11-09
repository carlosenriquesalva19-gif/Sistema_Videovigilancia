<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (! Auth::check()) return view('auth.login');
        return Auth::user()->isAdmin() ? redirect()->route('dashboard') : redirect()->route('portal');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $key = Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Demasiados intentos. Espera un minuto e inténtalo de nuevo.']);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'El correo o la contraseña no son correctos.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        AuditLog::create(['user_id' => Auth::id(), 'action' => 'login', 'description' => 'Inicio de sesión', 'ip_address' => $request->ip()]);

        $destination = $request->user()->isAdmin() ? route('dashboard') : route('portal');
        $request->session()->forget('url.intended');
        return redirect()->to($destination);
    }

    public function destroy(Request $request): RedirectResponse
    {
        AuditLog::create(['user_id' => Auth::id(), 'action' => 'logout', 'description' => 'Cierre de sesión', 'ip_address' => $request->ip()]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
