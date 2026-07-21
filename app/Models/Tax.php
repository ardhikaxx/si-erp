<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use SoftDeletes;

    protected $table = 'taxes';

    protected $fillable = ['name', 'code', 'rate', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'rate' => 'decimal:2'];
    }
}
