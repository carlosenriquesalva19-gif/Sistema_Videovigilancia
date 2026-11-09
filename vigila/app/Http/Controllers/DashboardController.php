<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Camera;
use App\Models\Recording;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $recordings = Recording::query();
        $storedBytes = (clone $recordings)->sum('size_bytes');
        $disk = config('vigila.video_disk');

        $costs = [
            'compute' => round(config('vigila.estimated_ec2_instances') * 24 * 30 * config('vigila.rates.ec2_hour'), 2),
            'storage' => round(($storedBytes / 1073741824) * config('vigila.rates.s3_gb_month'), 2),
            'database' => round(24 * 30 * config('vigila.rates.db_hour'), 2),
        ];
        $costs['total'] = round(array_sum($costs), 2);

        return view('dashboard', [
            'cameraCount' => Camera::count(),
            'activeCameras' => Camera::where('status', 'active')->count(),
            'recordingCount' => (clone $recordings)->count(),
            'storedBytes' => $storedBytes,
            'costs' => $costs,
            'recentRecordings' => Recording::with('camera')->latest('recorded_at')->limit(6)->get(),
            'recentActivity' => AuditLog::with('user')->latest()->limit(5)->get(),
            'videoDisk' => $disk,
            's3Configured' => $disk === 's3' && filled(config('filesystems.disks.s3.bucket')),
        ]);
    }

    public function costs(): View
    {
        $storedBytes = Recording::sum('size_bytes');
        $compute = round(config('vigila.estimated_ec2_instances') * 720 * config('vigila.rates.ec2_hour'), 2);
        $storage = round(($storedBytes / 1073741824) * config('vigila.rates.s3_gb_month'), 2);
        $database = round(720 * config('vigila.rates.db_hour'), 2);

        return view('costs', compact('storedBytes', 'compute', 'storage', 'database'));
    }

    public function security(): View
    {
        return view('security', ['events' => AuditLog::with('user')->latest()->limit(30)->get(), 'videoDisk' => config('vigila.video_disk')]);
    }

    public function operations(): View
    {
        return view('operations', ['videoDisk' => config('vigila.video_disk')]);
    }
}
