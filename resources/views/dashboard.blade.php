<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>QuickBooks Export Dashboard</title>
    <style>
        :root {
            --green: #2ca01c;
            --blue: #0077c5;
            --gray-bg: #f5f7fa;
            --card-bg: #ffffff;
            --border: #dce0e6;
            --text-main: #1a1a1a;
            --text-muted: #6b7280;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--gray-bg);
            color: var(--text-main);
        }

        .page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px 60px;
        }

        h1 {
            margin-top: 0;
            font-size: 26px;
        }

        .subtitle {
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 2fr);
            gap: 20px;
            align-items: flex-start;
        }

        .card {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 18px 20px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.04);
        }

        .card h2 {
            margin-top: 0;
            font-size: 18px;
            margin-bottom: 6px;
        }

        .card p {
            margin: 4px 0;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-success {
            background: #ecfdf3;
            color: #166534;
        }

        .badge-danger {
            background: #fef2f2;
            color: #b91c1c;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.15s ease, transform 0.05s ease;
            margin-top: 8px;
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn-primary {
            background: var(--green);
            color: #ffffff;
        }

        .btn-secondary {
            background: var(--blue);
            color: #ffffff;
        }

        .btn-outline {
            background: #ffffff;
            color: var(--blue);
            border: 1px solid var(--blue);
        }

        .small {
            font-size: 12px;
            color: var(--text-muted);
        }

        .field {
            margin-bottom: 12px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        select, input[type="date"] {
            width: 100%;
            padding: 7px 10px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 14px;
        }

        .row-inline {
            display: flex;
            gap: 10px;
        }

        .row-inline > div {
            flex: 1;
        }

        .hint {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 3px;
        }

        .alert {
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .alert-success {
            background: #ecfdf3;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .pill-group {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 6px;
        }

        .pill {
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
            color: #4b5563;
            background: #f9fafb;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <h1>QuickBooks Export Dashboard</h1>
    <p class="subtitle">Connect your QuickBooks company and export data like invoices, vendors, customers, and items with filters.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid">
        {{-- LEFT: Connection status --}}
        <div class="card">
            <h2>QuickBooks connection</h2>

            @if ($isConnected)
                <p>
                    <span class="badge badge-success">Connected</span>
                </p>
                <p class="small">
                    Your QuickBooks company is connected. You can safely run exports below.
                </p>
            @else
                <p>
                    <span class="badge badge-danger">Not connected</span>
                </p>
                <p class="small">
                    Connect your QuickBooks Online company before running any export.
                </p>
            @endif

            <a href="{{ route('qbo.connect') }}" class="btn btn-secondary">
                {{ $isConnected ? 'Reconnect QuickBooks' : 'Connect QuickBooks' }}
            </a>

            <div style="margin-top: 16px;">
                <p class="small">
                    CSV exports are streamed and optimized for very large datasets.<br>
                    Excel exports are better for smaller, more focused reports.
                </p>
            </div>
        </div>

        {{-- RIGHT: Export form --}}
        <div class="card">
            <h2>Run export</h2>
            <p class="small" style="margin-bottom: 10px;">
                Choose what you want to export, the format, and optionally a date filter.
            </p>

            @if (! $isConnected)
                <p class="alert alert-error" style="margin-top: 0;">
                    Please connect QuickBooks first before running an export.
                </p>
            @else
                <form method="POST" action="{{ route('qbo.export.run') }}">
                    @csrf

                    <div class="field">
                        <label for="export_type">Export type</label>
                        <select name="export_type" id="export_type" required>
                            <option value="invoice">Invoices</option>
                            <option value="vendor">Vendors</option>
                            <option value="customer">Customers</option>
                            <option value="item">Items</option>
                        </select>
                        <div class="pill-group">
                            <span class="pill">Invoices → sales history</span>
                            <span class="pill">Vendors → payables</span>
                            <span class="pill">Customers → receivables</span>
                            <span class="pill">Items → products/services</span>
                        </div>
                    </div>

                    <div class="field">
                        <label for="format">Format</label>
                        <select name="format" id="format" required>
                            <option value="csv">CSV (fast, very large datasets)</option>
                            <option value="excel">Excel (.xlsx)</option>
                        </select>
                        <p class="hint">
                            Use CSV for millions of rows; Excel is best for smaller, filtered exports.
                        </p>
                    </div>

                    <div class="field">
                        <label for="filter_type">Filter by date (optional)</label>
                        <select name="filter_type" id="filter_type">
                            <option value="none">No filter (all data)</option>
                            <option value="txn_date">Transaction date (TxnDate)</option>
                            <option value="created_time">Created time (MetaData.CreateTime)</option>
                            <option value="updated_time">Updated time (MetaData.LastUpdatedTime)</option>
                        </select>
                        <p class="hint">
                            Leave as "No filter" to export everything for that type.
                        </p>
                    </div>

                    <div class="row-inline">
                        <div class="field">
                            <label for="from_date">From date</label>
                            <input type="date" name="from_date" id="from_date">
                        </div>
                        <div class="field">
                            <label for="to_date">To date</label>
                            <input type="date" name="to_date" id="to_date">
                        </div>
                    </div>
                    <p class="hint">
                        If you choose a filter type but leave dates empty, no date filter is applied.
                        Set one or both dates to narrow results.
                    </p>

                    <button type="submit" class="btn btn-primary">
                        Run export
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
</body>
</html>
