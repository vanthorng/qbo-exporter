@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4">

    {{-- Page title --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-800">Create Export Schedule</h1>
        <p class="text-sm text-slate-500 mt-1">
            Define how often and which invoices should be exported automatically.
        </p>
    </div>

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="font-semibold mb-1">Whoops! There were some problems with your input.</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Success message --}}
    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <form method="POST" action="{{ route('export-schedules.store') }}" class="p-5 space-y-5">
            @csrf

            {{-- Schedule Name --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Schedule Name
                </label>
                <input
                    type="text"
                    name="name"
                    required
                    value="{{ old('name') }}"
                    class="w-full rounded-md border text-sm
                           @error('name') border-red-400 focus:border-red-500 focus:ring-red-500
                           @else border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror
                           px-3 py-2"
                    placeholder="e.g. Last Month – Daily"
                >
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Filter Type --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Filter Data Range
                </label>
                <select
                    name="filter_type"
                    id="filter_type"
                    class="w-full rounded-md border text-sm px-3 py-2
                           @error('filter_type') border-red-400 focus:border-red-500 focus:ring-red-500
                           @else border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror"
                >
                    <option value="last_month" {{ old('filter_type') == 'last_month' ? 'selected' : '' }}>
                        Last Month
                    </option>
                    <option value="last_7_days" {{ old('filter_type') == 'last_7_days' ? 'selected' : '' }}>
                        Last 7 Days
                    </option>
                    <option value="custom" {{ old('filter_type') == 'custom' ? 'selected' : '' }}>
                        Custom Range
                    </option>
                </select>
                @error('filter_type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Custom Date Range --}}
            <div id="custom_range_group"
                 class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ old('filter_type') === 'custom' ? '' : 'hidden' }}">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        From Date
                    </label>
                    <input
                        type="date"
                        name="from_date"
                        value="{{ old('from_date') }}"
                        class="w-full rounded-md border text-sm px-3 py-2
                               @error('from_date') border-red-400 focus:border-red-500 focus:ring-red-500
                               @else border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror"
                    >
                    @error('from_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        To Date
                    </label>
                    <input
                        type="date"
                        name="to_date"
                        value="{{ old('to_date') }}"
                        class="w-full rounded-md border text-sm px-3 py-2
                               @error('to_date') border-red-400 focus:border-red-500 focus:ring-red-500
                               @else border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror"
                    >
                    @error('to_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Frequency --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Frequency
                </label>
                <select
                    name="frequency"
                    class="w-full rounded-md border text-sm px-3 py-2
                           @error('frequency') border-red-400 focus:border-red-500 focus:ring-red-500
                           @else border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 @enderror"
                >
                    <option value="every_1_minute" {{ old('frequency') == 'every_1_minute' ? 'selected' : '' }}>
                        Every 1 Minute
                    </option>
                    <option value="every_10_minutes" {{ old('frequency') == 'every_10_minutes' ? 'selected' : '' }}>
                        Every 10 Minutes
                    </option>
                    <option value="hourly" {{ old('frequency') == 'hourly' ? 'selected' : '' }}>
                        Hourly
                    </option>
                    <option value="daily" {{ old('frequency') == 'daily' ? 'selected' : '' }}>
                        Daily
                    </option>
                </select>
                @error('frequency')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Active --}}
            <div class="flex items-center gap-2">
                <input
                    id="is_active"
                    type="checkbox"
                    name="is_active"
                    class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    {{ old('is_active', 'on') ? 'checked' : '' }}
                >
                <label for="is_active" class="text-sm text-slate-700">
                    Active
                </label>
            </div>

            {{-- Buttons --}}
            <div class="pt-2 flex items-center gap-2">
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md
                           bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2
                           focus:ring-offset-2 focus:ring-indigo-500">
                    Save Schedule
                </button>

                <a href="{{ route('export-schedules.index') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md
                          border border-slate-300 text-slate-700 bg-white hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterSelect = document.getElementById('filter_type');
    const customGroup  = document.getElementById('custom_range_group');

    function toggleCustomRange() {
        if (filterSelect.value === 'custom') {
            customGroup.classList.remove('hidden');
        } else {
            customGroup.classList.add('hidden');
        }
    }

    filterSelect.addEventListener('change', toggleCustomRange);
    toggleCustomRange();
});
</script>
@endsection
