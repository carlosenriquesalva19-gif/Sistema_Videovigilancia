<?php

namespace Database\Seeders;

use App\Models\Camera;
use App\Models\Recording;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@vigila.test'], ['name' => 'Administración VIGILA', 'password' => 'Cambiar123!']);
        if ($admin->role !== 'admin') {
            $admin->forceFill(['role' => 'admin'])->save();
        }

        $cameras = [
            ['name' => 'Acceso principal', 'location' => 'Sede Lima · Puerta norte', 'status' => 'active', 'retention_days' => 30],
            ['name' => 'Almacén central', 'location' => 'Sede Lima · Almacén', 'status' => 'active', 'retention_days' => 60],
            ['name' => 'Estacionamiento', 'location' => 'Sede Lima · Nivel 1', 'status' => 'maintenance', 'retention_days' => 30],
            ['name' => 'Recepción', 'location' => 'Sede Arequipa · Lobby', 'status' => 'active', 'retention_days' => 15],
        ];

        foreach ($cameras as $data) {
            $camera = Camera::firstOrCreate(['name' => $data['name']], $data);
            Recording::firstOrCreate(['camera_id' => $camera->id, 'title' => 'Grabación de muestra · '.now()->format('d/m/Y')], [
                'recorded_at' => now()->subHours(rand(1, 72)), 'duration_seconds' => rand(30, 540),
                'size_bytes' => rand(60, 850) * 1024 * 1024, 'storage_class' => 'STANDARD', 'status' => 'ready',
            ]);
        }

        $viewer = User::updateOrCreate(
            ['email' => 'usuario@vigila.test'],
            ['name' => 'Usuario VIGILA', 'password' => 'Usuario123!', 'role' => 'user'],
        );
        $viewer->cameras()->sync(Camera::query()->pluck('id'));
    }
}
