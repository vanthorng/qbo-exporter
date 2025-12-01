<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>QuickBooks Export</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        label { display: block; margin-top: 10px; }
        select, input[type="date"] { padding: 6px; width: 260px; }
        button { margin-top: 20px; padding: 8px 16px; }
        .row { margin-bottom: 10px; }
        .alert { padding: 8px 12px; border-radius: 4px; margin-bottom: 15px; }
        .alert-success { background: #e6ffed; color: #155724; }
        .alert-error { background: #ffe6e6; color: #721c24; }
    </style>
</head>
<body>
    <h1>QuickBooks Export</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('qbo.export.run') }}">
        @csrf

        <div class="row">
            <label for="export_type">Export type</label>
            <select name="export_type" id="export_type" required>
                <option value="invoice">Invoices</option>
                <option value="vendor">Vendors</option>
                <option value="customer">Customers</option>
                <option value="item">Items</option>
            </select>
        </div>

        <div class="row">
            <label for="format">Format</label>
            <select name="format" id="format" required>
                <option value="csv">CSV (fast, large datasets)</option>
                <option value="excel">Excel (for smaller datasets)</option>
            </select>
        </div>

        <div class="row">
            <label for="filter_type">Filter by date</label>
            <select name="filter_type" id="filter_type">
                <option value="none">No filter (all data)</option>
                <option value="txn_date">Transaction date (TxnDate)</option>
                <option value="created_time">Created time (MetaData.CreateTime)</option>
                <option value="updated_time">Updated time (MetaData.LastUpdatedTime)</option>
            </select>
        </div>

        <div class="row">
            <label>From date</label>
            <input type="date" name="from_date">
        </div>

        <div class="row">
            <label>To date</label>
            <input type="date" name="to_date">
        </div>

        <button type="submit">Run Export</button>
    </form>
</body>
</html>
