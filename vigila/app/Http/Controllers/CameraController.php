<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Camera;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CameraController extends Controller
{
    public function index(): View
    {
        return view('cameras.index', ['cameras' => Camera::withCount('recordings')->latest()->get()]);
    }

    public function create(): View { return view('cameras.create', ['camera' => new Camera()]); }

    public function store(Request $request): RedirectResponse
    {
        $camera = Camera::create($this->validated($request));
        $this->audit($request, 'camera.created', "Cámara creada: {$camera->name}");
        return redirect()->route('cameras.index')->with('success', 'La cámara se registró correctamente.');
    }

    public function edit(Camera $camera): View { return view('cameras.edit', compact('camera')); }

    public function update(Request $request, Camera $camera): RedirectResponse
    {
        $camera->update($this->validated($request));
        $this->audit($request, 'camera.updated', "Cámara actualizada: {$camera->name}");
        return redirect()->route('cameras.index')->with('success', 'Los cambios de la cámara se guardaron.');
    }

    public function destroy(Request $request, Camera $camera): RedirectResponse
    {
        $name = $camera->name;
        foreach ($camera->recordings()->whereNotNull('file_path')->get() as $recording) {
            Storage::disk(config('vigila.video_disk'))->delete($recording->file_path);
        }
        $camera->delete();
        $this->audit($request, 'camera.deleted', "Cámara eliminada: {$name}");
        return redirect()->route('cameras.index')->with('success', 'La cámara y sus registros quedaron eliminados.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'], 'location' => ['required', 'string', 'max:180'],
            'stream_url' => ['nullable', 'url', 'starts_with:http://,https://', 'max:255'], 'aws_stream_name' => ['nullable', 'string', 'max:256'], 'status' => ['required', 'in:active,maintenance,offline'],
            'retention_days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);
    }

    private function audit(Request $request, string $action, string $description): void
    {
        AuditLog::create(['user_id' => $request->user()->id, 'action' => $action, 'description' => $description, 'ip_address' => $request->ip()]);
    }
}
