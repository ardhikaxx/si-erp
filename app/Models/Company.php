<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'alias', 'logo', 'address', 'phone', 'email', 'website',
        'tax_id', 'currency', 'timezone', 'date_format', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}
