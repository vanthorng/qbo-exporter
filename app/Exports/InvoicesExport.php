<?php

namespace App\Exports;

use App\Models\Invoice;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class InvoicesExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    public function __construct(
        protected ?Carbon $from = null,
        protected ?Carbon $to = null
    ) {}

    /**
     * Build the base query (NO ->get()).
     */
    public function query()
    {
        $query = Invoice::query();

        if ($this->from) {
            $query->whereDate('issue_date', '>=', $this->from->toDateString());
        }

        if ($this->to) {
            $query->whereDate('issue_date', '<=', $this->to->toDateString());
        }

        // Only invoice header here; items can be another sheet later if needed
        return $query;
    }

    /**
     * Map each invoice row.
     */
    public function map($invoice): array
    {
        return [
            $invoice->id,
            $invoice->invoice_number,
            $invoice->customer_id,
            $invoice->issue_date,
            $invoice->due_date,
            $invoice->status,
            $invoice->currency,
            $invoice->subtotal,
            $invoice->discount_total,
            $invoice->tax_total,
            $invoice->total,
            $invoice->paid_total,
            $invoice->balance_due,
            $invoice->created_at,
        ];
    }

    /**
     * Column headers.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Invoice Number',
            'Customer ID',
            'Issue Date',
            'Due Date',
            'Status',
            'Currency',
            'Subtotal',
            'Discount Total',
            'Tax Total',
            'Total',
            'Paid Total',
            'Balance Due',
            'Created At',
        ];
    }

    /**
     * How many rows per chunk.
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
