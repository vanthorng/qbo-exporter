<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>QBO Export Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; }
        .btn {
            display: inline-block;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
            margin-top: 10px;
        }
        .btn-primary { background: #2ca01c; color: #fff; }
        .btn-secondary { background: #0077c5; color: #fff; }
        .alert { padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background: #e6ffed; color: #155724; }
        .alert-error { background: #ffe6e6; color: #721c24; }
    </style>
</head>
<body>
    <h1>QuickBooks Export Dashboard</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <p>
        <strong>Step 1:</strong> Connect or reconnect your QuickBooks company.
    </p>
    <a href="{{ route('qbo.connect') }}" class="btn btn-secondary">Connect QuickBooks</a>

    <hr>

    <p>
        <strong>Step 2:</strong> After connected, export invoices from QuickBooks Online.
    </p>
    <a href="{{ route('qbo.export.vendors') }}" class="btn btn-primary">
        Export Vendors (CSV)
    </a>

    <p style="margin-top: 20px; color: #555;">
        The export is streamed in chunks, so it can handle very large datasets
        (hundreds of thousands of rows or more, limited by QBO API and your server).
    </p>
</body>
</html>
