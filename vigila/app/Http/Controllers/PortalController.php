<?php

namespace App\Http\Controllers;

use App\Models\Camera;
use App\Models\Recording;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function index(Request $request): View
    {
        $cameraIds = $request->user()->cameras()->pluck('cameras.id');
        return view('portal.index', [
            'cameras' => $request->user()->cameras()->withCount('recordings')->orderBy('name')->get(),
            'recordings' => Recording::with('camera')->whereIn('camera_id', $cameraIds)->latest('recorded_at')->limit(8)->get(),
        ]);
    }

    public function camera(Request $request, Camera $camera): View
    {
        $allowed = $request->user()->cameras()->whereKey($camera->id)->exists();
        abort_unless($allowed, 404);

        return view('portal.camera', [
            'camera' => $camera,
            'recordings' => $camera->recordings()->latest('recorded_at')->paginate(12),
            'liveEndpoint' => route('portal.cameras.live-session', $camera),
        ]);
    }
}
