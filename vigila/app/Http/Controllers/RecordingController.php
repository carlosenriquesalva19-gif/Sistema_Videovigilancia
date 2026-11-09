<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Camera;
use App\Models\Recording;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecordingController extends Controller
{
    public function index(Request $request): View
    {
        $recordings = Recording::with('camera')->latest('recorded_at');
        if ($request->filled('camera_id')) $recordings->where('camera_id', $request->integer('camera_id'));
        if ($request->filled('from')) $recordings->whereDate('recorded_at', '>=', $request->date('from'));
        if ($request->filled('to')) $recordings->whereDate('recorded_at', '<=', $request->date('to'));

        return view('recordings.index', ['recordings' => $recordings->paginate(12)->withQueryString(), 'cameras' => Camera::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('recordings.create', ['cameras' => Camera::where('status', '!=', 'offline')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $uploadMessage = $this->uploadFailureMessage($request);
        $validated = $request->validate([
            'camera_id' => ['required', 'exists:cameras,id'], 'title' => ['required', 'string', 'max:180'],
            'recorded_at' => ['required', 'date'], 'duration_seconds' => ['required', 'integer', 'min:1', 'max:86400'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm,video/x-matroska', 'max:512000'],
        ], [
            'video.uploaded' => $uploadMessage,
            'video.max' => 'El video no puede superar los 500 MB.',
            'video.mimetypes' => 'El formato debe ser MP4, MOV, WebM o MKV.',
        ]);

        $filePath = null;
        $size = 0;
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $filePath = $file->store('recordings/'.now()->format('Y/m'), config('vigila.video_disk'));
            $size = $file->getSize();
        }

        $recording = Recording::create([
            'camera_id' => $validated['camera_id'], 'title' => $validated['title'], 'recorded_at' => $validated['recorded_at'],
            'duration_seconds' => $validated['duration_seconds'], 'file_path' => $filePath, 'size_bytes' => $size,
            'storage_class' => 'STANDARD', 'status' => 'ready',
        ]);
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'recording.created', 'description' => "Grabación registrada: {$recording->title}", 'ip_address' => $request->ip()]);

        return redirect()->route('recordings.index')->with('success', 'La grabación se registró correctamente.');
    }

    public function download(Request $request, Recording $recording): Response|BinaryFileResponse|StreamedResponse|RedirectResponse
    {
        abort_unless($request->user()->isAdmin() || $request->user()->cameras()->whereKey($recording->camera_id)->exists(), 403);
        abort_unless($recording->file_path, 404, 'Esta grabación no tiene un archivo asociado.');
        $diskName = config('vigila.video_disk');
        $disk = Storage::disk($diskName);
        abort_unless($disk->exists($recording->file_path), 404, 'No se encontró el archivo de video.');
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'recording.accessed', 'description' => "Acceso a grabación: {$recording->title}", 'ip_address' => $request->ip()]);

        if ($diskName === 's3' && $disk->providesTemporaryUrls()) {
            return redirect()->away($disk->temporaryUrl($recording->file_path, now()->addMinutes(5)));
        }

        $localPath = $disk->path($recording->file_path);
        if (file_exists($localPath)) {
            return response()->file($localPath, [
                'Content-Disposition' => 'inline; filename="'.basename($recording->file_path).'"',
                'Accept-Ranges' => 'bytes',
            ]);
        }

        return $disk->download($recording->file_path);
    }

    public function destroy(Request $request, Recording $recording): RedirectResponse
    {
        if ($recording->file_path) Storage::disk(config('vigila.video_disk'))->delete($recording->file_path);
        $title = $recording->title;
        $recording->delete();
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'recording.deleted', 'description' => "Grabación eliminada: {$title}", 'ip_address' => $request->ip()]);
        return redirect()->route('recordings.index')->with('success', 'La grabación fue eliminada.');
    }

    private function uploadFailureMessage(Request $request): string
    {
        $errorCode = $request->file('video')?->getError();
        $reason = match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'el video supera upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'el formulario limita el tamaño del video',
            UPLOAD_ERR_PARTIAL => 'la carga quedó incompleta',
            UPLOAD_ERR_NO_TMP_DIR => 'PHP no encuentra su carpeta temporal',
            UPLOAD_ERR_CANT_WRITE => 'PHP no pudo escribir el archivo temporal; revisa espacio y permisos',
            UPLOAD_ERR_EXTENSION => 'una extensión de PHP detuvo la carga',
            default => 'PHP no recibió el archivo completo',
        };

        return sprintf(
            'No se pudo recibir el video: %s. Límites del servidor actual: upload_max_filesize=%s, post_max_size=%s, file_uploads=%s. Usa iniciar-vigila.ps1 y revisa que el video no supere 500 MB.',
            $reason,
            ini_get('upload_max_filesize'),
            ini_get('post_max_size'),
            ini_get('file_uploads'),
        );
    }
}
