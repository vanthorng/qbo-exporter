@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4">

    {{-- Breadcrumb / header --}}
    <div class="flex items-center justify-between gap-3 mb-6">
        <div>
            <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">
                Invoice
            </p>
            <h1 class="text-2xl font-semibold text-slate-800">
                {{ $invoice->invoice_number }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Detailed view of this invoice and its line items.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('invoices.index') }}"
               class="inline-flex items-center px-3 py-1.5 text-sm rounded-md border border-slate-300 text-slate-700 bg-white hover:bg-slate-50">
                ← Back to Invoices
            </a>
        </div>
    </div>

    {{-- Top info: summary + meta --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
        {{-- Summary card --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-400">
                        Status
                    </div>
                    @php
                        $status = strtolower($invoice->status);
                        $statusClasses = match ($status) {
                            'paid'      => 'bg-emerald-100 text-emerald-700',
                            'sent'      => 'bg-blue-100 text-blue-700',
                            'overdue'   => 'bg-red-100 text-red-700',
                            'cancelled' => 'bg-slate-200 text-slate-700',
                            'draft'     => 'bg-amber-100 text-amber-800',
                            default     => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClasses }}">
                        {{ ucfirst($status) }}
                    </span>
                </div>

                <div class="text-right">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-400">
                        Total
                    </div>
                    <div class="text-2xl font-semibold text-slate-800">
                        <span class="text-sm text-slate-400 mr-1">{{ $invoice->currency ?? 'USD' }}</span>
                        {{ number_format($invoice->total, 2) }}
                    </div>
                    <div class="mt-1 text-xs text-slate-500">
                        Paid: {{ number_format($invoice->paid_total, 2) }} •
                        Balance: {{ number_format($invoice->balance_due, 2) }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-2">
                <div>
                    <div class="text-xs uppercase tracking-wide text-slate-400 mb-0.5">
                        Customer ID
                    </div>
                    <div class="text-sm text-slate-800">
                        @if($invoice->customer_id)
                            #{{ $invoice->customer_id }}
                        @else
                            <span class="text-slate-400">N/A</span>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="text-xs uppercase tracking-wide text-slate-400 mb-0.5">
                        Issue Date
                    </div>
                    <div class="text-sm text-slate-800">
                        {{ optional($invoice->issue_date)->format('Y-m-d') ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs uppercase tracking-wide text-slate-400 mb-0.5">
                        Due Date
                    </div>
                    <div class="text-sm text-slate-800">
                        {{ optional($invoice->due_date)->format('Y-m-d') ?? '—' }}
                    </div>
                </div>
            </div>

            @if($invoice->reference || $invoice->notes)
                <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($invoice->reference)
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-400 mb-0.5">
                                Reference
                            </div>
                            <div class="text-sm text-slate-800">
                                {{ $invoice->reference }}
                            </div>
                        </div>
                    @endif

                    @if($invoice->notes)
                        <div>
                            <div class="text-xs uppercase tracking-wide text-slate-400 mb-0.5">
                                Notes
                            </div>
                            <div class="text-sm text-slate-700 whitespace-pre-line">
                                {{ $invoice->notes }}
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Totals / meta card --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 space-y-3">
            <h2 class="text-sm font-semibold text-slate-700 mb-2">Amounts</h2>

            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Subtotal</span>
                <span class="text-slate-800">
                    {{ number_format($invoice->subtotal, 2) }}
                </span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Discount</span>
                <span class="text-slate-800">
                    - {{ number_format($invoice->discount_total, 2) }}
                </span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Tax</span>
                <span class="text-slate-800">
                    {{ number_format($invoice->tax_total, 2) }}
                </span>
            </div>

            <div class="border-t border-dashed border-slate-200 my-2"></div>

            <div class="flex justify-between text-sm font-semibold">
                <span class="text-slate-700">Total</span>
                <span class="text-slate-900">
                    {{ number_format($invoice->total, 2) }}
                </span>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 space-y-1 text-xs text-slate-500">
                <div>
                    Created: {{ $invoice->created_at?->format('Y-m-d H:i') ?? '—' }}
                </div>
                <div>
                    Updated: {{ $invoice->updated_at?->format('Y-m-d H:i') ?? '—' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Items table --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <span class="text-sm font-medium text-slate-700">Invoice Items</span>
            <span class="text-xs text-slate-500">
                {{ $invoice->items->count() }} items
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2 text-left">#</th>
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Description</th>
                        <th class="px-4 py-2 text-right">Qty</th>
                        <th class="px-4 py-2 text-right">Unit Price</th>
                        <th class="px-4 py-2 text-right">Discount</th>
                        <th class="px-4 py-2 text-right">Tax %</th>
                        <th class="px-4 py-2 text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoice->items as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2 text-slate-500">
                                {{ $item->id }}
                            </td>
                            <td class="px-4 py-2 font-medium text-slate-800">
                                {{ $item->name }}
                            </td>
                            <td class="px-4 py-2 text-slate-600">
                                <span class="block max-w-xs truncate" title="{{ $item->description }}">
                                    {{ $item->description }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right text-slate-700">
                                {{ $item->quantity }}
                            </td>
                            <td class="px-4 py-2 text-right text-slate-700">
                                {{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="px-4 py-2 text-right text-slate-700">
                                {{ number_format($item->discount_amount, 2) }}
                            </td>
                            <td class="px-4 py-2 text-right text-slate-700">
                                {{ $item->tax_rate }}
                            </td>
                            <td class="px-4 py-2 text-right text-slate-900 font-semibold">
                                {{ number_format($item->line_total, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400 text-sm">
                                No items on this invoice.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
