@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Export Schedules</h1>
            <p class="text-sm text-slate-500 mt-1">
                Manage automated invoice export jobs and their last runs.
            </p>
        </div>

        <div>
            <a href="{{ route('export-schedules.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md
                      bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none
                      focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <span class="mr-1.5 text-lg leading-none">＋</span>
                New Schedule
            </a>
        </div>
    </div>

    {{-- Flash success --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white shadow-sm rounded-xl border border-slate-200">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <span class="text-sm font-medium text-slate-700">Schedules</span>
            <span class="text-xs text-slate-500">
                {{ $schedules->count() }} total
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Filter</th>
                        <th class="px-4 py-2 text-left">Frequency</th>
                        <th class="px-4 py-2 text-center">Active</th>
                        <th class="px-4 py-2 text-left">Last Run</th>
                        <th class="px-4 py-2 text-left">Last File</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schedules as $schedule)
                        <tr class="hover:bg-slate-50">
                            {{-- Name --}}
                            <td class="px-4 py-2 font-medium text-slate-800">
                                {{ $schedule->name }}
                            </td>

                            {{-- Filter --}}
                            <td class="px-4 py-2 text-slate-700">
                                <div class="text-xs uppercase tracking-wide text-slate-400 mb-0.5">
                                    {{ str_replace('_', ' ', $schedule->filter_type) }}
                                </div>
                                @if($schedule->filter_type === 'custom')
                                    <div class="text-xs text-slate-500">
                                        {{ optional($schedule->from_date)->format('Y-m-d') ?? '—' }}
                                        <span class="mx-1">→</span>
                                        {{ optional($schedule->to_date)->format('Y-m-d') ?? '—' }}
                                    </div>
                                @endif
                            </td>

                            {{-- Frequency --}}
                            <td class="px-4 py-2 text-slate-700">
                                @php
                                    $freqLabel = match ($schedule->frequency) {
                                        'every_1_minute'   => 'Every 1 Minute',
                                        'every_10_minutes' => 'Every 10 Minutes',
                                        'hourly'           => 'Hourly',
                                        'daily'            => 'Daily',
                                        default            => $schedule->frequency,
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-xs text-slate-700">
                                    {{ $freqLabel }}
                                </span>
                            </td>

                            {{-- Active --}}
                            <td class="px-4 py-2 text-center">
                                @if($schedule->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 text-xs text-emerald-700 font-medium">
                                        ● Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-200 text-xs text-slate-600 font-medium">
                                        ● Inactive
                                    </span>
                                @endif
                            </td>

                            {{-- Last run --}}
                            <td class="px-4 py-2 text-slate-700">
                                @if($schedule->last_run_at)
                                    <div class="text-sm">
                                        {{ $schedule->last_run_at->format('Y-m-d H:i') }}
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Never run</span>
                                @endif
                            </td>

                            {{-- Last file --}}
                            <td class="px-4 py-2 text-slate-700">
                                @if($schedule->last_file_path)
                                    <div class="truncate max-w-xs text-xs text-slate-600">
                                        {{ $schedule->last_file_path }}
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">No file yet</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-400 text-sm">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-lg">
                                        ⏱
                                    </div>
                                    <p class="font-medium text-slate-600">No schedules found</p>
                                    <p class="text-xs text-slate-400">
                                        Click “New Schedule” to create your first export job.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
