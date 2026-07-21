<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    protected $fillable = [
        'stock_opname_id', 'product_id', 'system_quantity',
        'actual_quantity', 'difference', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'system_quantity' => 'decimal:2',
            'actual_quantity' => 'decimal:2',
            'difference' => 'decimal:2',
        ];
    }

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
