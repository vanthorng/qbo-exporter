@extends('layouts.app')

@section('content')

<div class="container">

    <h1>Invoice: {{ $invoice->invoice_number }}</h1>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Customer ID:</strong> {{ $invoice->customer_id }}</p>
            <p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
            <p><strong>Issue Date:</strong> {{ $invoice->issue_date }}</p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date }}</p>
            <p><strong>Currency:</strong> {{ $invoice->currency }}</p>
            <p><strong>Total:</strong> ${{ number_format($invoice->total, 2) }}</p>
        </div>
    </div>

    <h3>Invoice Items</h3>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Discount</th>
                <th>Tax (%)</th>
                <th>Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->unit_price }}</td>
                <td>{{ $item->discount_amount }}</td>
                <td>{{ $item->tax_rate }}</td>
                <td>${{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection
