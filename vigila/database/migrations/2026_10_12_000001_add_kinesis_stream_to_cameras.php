<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cameras', function (Blueprint $table): void {
            $table->string('aws_stream_name', 256)->nullable()->after('stream_url');
        });
    }

    public function down(): void
    {
        Schema::table('cameras', function (Blueprint $table): void { $table->dropColumn('aws_stream_name'); });
    }
};
