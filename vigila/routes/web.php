<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CameraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LiveStreamController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\RecordingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('cameras', CameraController::class)->except('show');
    Route::get('/recordings', [RecordingController::class, 'index'])->name('recordings.index');
    Route::get('/recordings/create', [RecordingController::class, 'create'])->name('recordings.create');
    Route::post('/recordings', [RecordingController::class, 'store'])->name('recordings.store');
    Route::delete('/recordings/{recording}', [RecordingController::class, 'destroy'])->name('recordings.destroy');
    Route::get('/costs', [DashboardController::class, 'costs'])->name('costs');
    Route::get('/security', [DashboardController::class, 'security'])->name('security');
    Route::get('/operations', [DashboardController::class, 'operations'])->name('operations');
    Route::resource('users', UserController::class)->except('show');
    Route::get('/cameras/{camera}/live', [LiveStreamController::class, 'adminShow'])->name('cameras.live');
    Route::get('/cameras/{camera}/live/session', [LiveStreamController::class, 'adminSession'])->name('cameras.live.session');
});

Route::middleware('auth')->get('/recordings/{recording}/download', [RecordingController::class, 'download'])->name('recordings.download');

Route::middleware(['auth', 'role:user'])->prefix('portal')->group(function (): void {
    Route::get('/', [PortalController::class, 'index'])->name('portal');
    Route::get('/cameras/{camera}', [PortalController::class, 'camera'])->name('portal.cameras.show');
    Route::get('/cameras/{camera}/live-session', [LiveStreamController::class, 'portalSession'])->name('portal.cameras.live-session');
});
