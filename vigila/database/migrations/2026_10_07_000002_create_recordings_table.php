<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recordings', function (Blueprint $table): void {
            $table->id(); $table->foreignId('camera_id')->constrained()->cascadeOnDelete(); $table->string('title'); $table->timestamp('recorded_at')->index();
            $table->unsignedInteger('duration_seconds')->default(0); $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('storage_class')->default('STANDARD'); $table->string('file_path')->nullable();
            $table->string('status')->default('ready'); $table->timestamps();
            $table->index(['camera_id', 'recorded_at']); $table->index('storage_class');
        });
    }

    public function down(): void { Schema::dropIfExists('recordings'); }
};
