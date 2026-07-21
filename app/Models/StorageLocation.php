<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorageLocation extends Model
{
    use SoftDeletes;

    protected $fillable = ['warehouse_id', 'name', 'code', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
