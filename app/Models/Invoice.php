<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'issue_date',
        'due_date',
        'status',
        'currency',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'paid_total',
        'balance_due',
        'reference',
        'notes',
        'terms',
        'is_recurring',
        'recurring_frequency',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // If you later have a Customer model, you can enable this:
    // public function customer()
    // {
    //     return $this->belongsTo(Customer::class);
    // }

    /**
     * Recalculate totals based on items.
     */
    public function recalcTotals(): void
    {
        $subtotal = $this->items->sum('line_total');

        // You can add more complex tax/discount logic here
        $this->subtotal       = $subtotal;
        $this->discount_total = $this->discount_total ?? 0;
        $this->tax_total      = $this->tax_total ?? 0;
        $this->total          = $subtotal - $this->discount_total + $this->tax_total;
        $this->balance_due    = $this->total - $this->paid_total;

        $this->save();
    }
}
