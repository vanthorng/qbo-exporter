<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a paginated list of invoices.
     */
    public function index(Request $request)
    {
        // Optional search
        $query = Invoice::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_id', $search)
                  ->orWhere('status', 'like', "%{$search}%");
        }

        // Order newest first, paginate 20 per page
        $invoices = $query->orderBy('id', 'desc')->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Display a specific invoice with its line items.
     */
    public function show(Invoice $invoice)
    {
        // Load invoice items
        $invoice->load('items');

        return view('invoices.show', compact('invoice'));
    }
}
