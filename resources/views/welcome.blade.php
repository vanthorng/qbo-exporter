<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>QBO Exporter</title>
</head>
<body>
    @if (session('status'))
        <p style="color: green">{{ session('status') }}</p>
    @endif
    @if (session('error'))
        <p style="color: red">{{ session('error') }}</p>
    @endif

    <h1>QuickBooks Exporter</h1>

    <p>
        <a href="{{ route('qbo.connect') }}">1. Connect QuickBooks</a>
    </p>

    <p>
        <a href="{{ route('qbo.export.invoices') }}">2. Export Invoices CSV</a>
    </p>
</body>
</html>
