<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('user')->after('email');
        });

        DB::table('users')->where('email', 'admin@vigila.test')->update(['role' => 'admin']);

        Schema::create('camera_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('camera_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['camera_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camera_user');
        Schema::table('users', function (Blueprint $table): void { $table->dropColumn('role'); });
    }
};
