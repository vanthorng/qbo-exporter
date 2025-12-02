@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Invoices</h1>
            <p class="text-sm text-slate-500">
                Browse, filter, and inspect your invoices.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('export-schedules.index') }}"
               class="inline-flex items-center px-3 py-1.5 text-sm border border-slate-300 rounded-md text-slate-700 hover:bg-slate-50">
                <span class="mr-1.5">⏱</span> Export Schedules
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white shadow-sm rounded-xl border border-slate-200 mb-6">
        <div class="p-4 md:p-5">
            <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Search --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Search
                    </label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Invoice #, customer, status…"
                        class="w-full rounded-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Status
                    </label>
                    <select
                        name="status"
                        class="w-full rounded-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">All</option>
                        <option value="draft"     {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent"      {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid"      {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue"   {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                {{-- From date --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Date from
                    </label>
                    <input
                        type="date"
                        name="from_date"
                        value="{{ request('from_date') }}"
                        class="w-full rounded-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                {{-- To date --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">
                        Date to
                    </label>
                    <input
                        type="date"
                        name="to_date"
                        value="{{ request('to_date') }}"
                        class="w-full rounded-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                {{-- Buttons --}}
                <div class="md:col-span-4 flex justify-end gap-2 pt-2">
                    <a href="{{ route('invoices.index') }}"
                       class="inline-flex items-center px-3 py-1.5 text-sm rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50">
                        Reset
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-3 py-1.5 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white shadow-sm rounded-xl border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <span class="text-sm font-medium text-slate-700">Invoice List</span>
            <span class="text-xs text-slate-500">
                @if($invoices->total())
                    Showing {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }}
                    of {{ $invoices->total() }} records
                @else
                    No invoices found
                @endif
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2 text-left w-16">#</th>
                        <th class="px-4 py-2 text-left">Invoice Number</th>
                        <th class="px-4 py-2 text-left">Customer</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-right">Total</th>
                        <th class="px-4 py-2 text-left">Issue Date</th>
                        <th class="px-4 py-2 text-left">Created</th>
                        <th class="px-4 py-2 text-center w-24">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2 text-slate-500">
                                {{ $invoice->id }}
                            </td>

                            <td class="px-4 py-2 font-medium text-slate-800">
                                {{ $invoice->invoice_number }}
                            </td>

                            <td class="px-4 py-2">
                                @if($invoice->customer_id)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-700">
                                        #{{ $invoice->customer_id }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">N/A</span>
                                @endif
                            </td>

                            <td class="px-4 py-2">
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
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>

                            <td class="px-4 py-2 text-right">
                                <span class="text-xs text-slate-400 mr-1">
                                    {{ $invoice->currency ?? 'USD' }}
                                </span>
                                <span class="font-semibold">
                                    {{ number_format($invoice->total, 2) }}
                                </span>
                            </td>

                            <td class="px-4 py-2 text-slate-700">
                                {{ optional($invoice->issue_date)->format('Y-m-d') ?? '—' }}
                            </td>

                            <td class="px-4 py-2 text-slate-700">
                                {{ $invoice->created_at?->format('Y-m-d H:i') }}
                            </td>

                            <td class="px-4 py-2 text-center">
                                <a href="{{ route('invoices.show', $invoice->id) }}"
                                   class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md border border-indigo-200 text-indigo-700 hover:bg-indigo-50">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-slate-400 text-sm">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-lg">
                                        🧾
                                    </div>
                                    <p class="font-medium text-slate-600">No invoices found</p>
                                    <p class="text-xs text-slate-400">
                                        Try changing your filters or search query.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="px-4 py-3 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                <div>
                    Page {{ $invoices->currentPage() }} of {{ $invoices->lastPage() }}
                </div>
                <div class="space-x-1">
                    {{ $invoices->onEachSide(1)->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
