<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceItem extends Model
{
    protected $fillable = [
        'sales_invoice_id', 'product_id', 'description', 'quantity',
        'price', 'discount', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'price' => 'decimal:2',
            'discount' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
