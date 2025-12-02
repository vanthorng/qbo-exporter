<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\ExportSchedule;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Carbon;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 👉 This runs every minute when scheduler is active
Schedule::call(function () {
    $now = Carbon::now();

    ExportSchedule::where('is_active', true)->get()->each(function (ExportSchedule $schedule) use ($now) {
        if (! $schedule->isDue($now)) {
            return;
        }

        [$from, $to] = $schedule->getDateRange($now);

        // Call the export command with range
        Artisan::call('invoice:export', [
            '--from' => $from?->toDateString(),
            '--to'   => $to?->toDateString(),
        ]);

        // Save info back on schedule
        $schedule->last_run_at    = $now;
        $schedule->last_file_path = 'exports/invoices_' . $now->format('Y_m_d_H_i') . '.xlsx';
        $schedule->save();
    });
})->everyMinute(); // core scheduler loop
