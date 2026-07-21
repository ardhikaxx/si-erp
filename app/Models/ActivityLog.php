<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'module', 'action', 'reference_type', 'reference_id',
        'description', 'data', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
