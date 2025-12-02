<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'name',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_amount',
        'discount_percent',
        'tax_rate',
        'line_total',
        'sort_order',
    ];

    protected $casts = [
        'quantity'        => 'float',
        'unit_price'      => 'float',
        'discount_amount' => 'float',
        'discount_percent'=> 'float',
        'tax_rate'        => 'float',
        'line_total'      => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // If you later have Product model:
    // public function product()
    // {
    //     return $this->belongsTo(Product::class);
    // }

    /**
     * Calculate line_total using quantity, price, discount & tax.
     */
    public function calculateLineTotal(): float
    {
        $base = $this->quantity * $this->unit_price;

        // Apply percentage discount
        if ($this->discount_percent > 0) {
            $base -= $base * ($this->discount_percent / 100);
        }

        // Apply absolute discount
        $base -= $this->discount_amount;

        // Apply tax
        if ($this->tax_rate > 0) {
            $base += $base * ($this->tax_rate / 100);
        }

        // Store and return
        $this->line_total = $base;
        $this->save();

        return $base;
    }
}
