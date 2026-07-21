<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = ['branch_id', 'name', 'code', 'address', 'phone', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function storageLocations()
    {
        return $this->hasMany(StorageLocation::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
