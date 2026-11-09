<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recording extends Model
{
    protected $fillable = ['camera_id', 'title', 'recorded_at', 'duration_seconds', 'size_bytes', 'storage_class', 'file_path', 'status'];

    protected function casts(): array
    {
        return ['recorded_at' => 'datetime'];
    }

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }
}
