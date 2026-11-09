<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Camera extends Model
{
    protected $fillable = ['name', 'location', 'stream_url', 'aws_stream_name', 'status', 'retention_days'];

    public function recordings(): HasMany
    {
        return $this->hasMany(Recording::class);
    }
}
