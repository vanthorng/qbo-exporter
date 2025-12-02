<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Invoice System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tailwind via CDN (good for dev/testing) --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900 antialiased">

    {{-- Top bar --}}
    <nav class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ url('/') }}" class="text-lg font-semibold tracking-tight text-slate-800">
                Invoice System
            </a>

            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('invoices.index') }}" class="text-slate-600 hover:text-slate-900">
                    Invoices
                </a>
                <a href="{{ route('export-schedules.index') }}" class="text-slate-600 hover:text-slate-900">
                    Export Schedules
                </a>
            </div>
        </div>
    </nav>

    <main class="py-6">
        @yield('content')
    </main>

</body>
</html>
