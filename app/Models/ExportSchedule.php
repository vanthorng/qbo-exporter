<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ExportSchedule extends Model
{
    protected $fillable = [
        'name',
        'filter_type',
        'from_date',
        'to_date',
        'frequency',
        'is_active',
        'last_run_at',
        'last_file_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'from_date' => 'date',
        'to_date' => 'date',
        'last_run_at' => 'datetime',
    ];

    /**
     * Check if this schedule is due now according to its frequency.
     */
    public function isDue(Carbon $now): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (! $this->last_run_at) {
            return true; // never run before
        }

        $diffMinutes = $this->last_run_at->diffInMinutes($now);

        return match ($this->frequency) {
            'every_1_minute'  => $diffMinutes >= 1,
            'every_10_minutes' => $diffMinutes >= 10,
            'hourly'           => $diffMinutes >= 60,
            'daily'            => $diffMinutes >= 60 * 24,
            default            => false,
        };
    }

    /**
     * Get from/to dates based on filter_type and now.
     */
    public function getDateRange(Carbon $now): array
    {
        if ($this->filter_type === 'last_month') {
            // Previous calendar month
            $from = (clone $now)->subMonthNoOverflow()->startOfMonth();
            $to   = (clone $now)->subMonthNoOverflow()->endOfMonth();
        } elseif ($this->filter_type === 'last_7_days') {
            $from = (clone $now)->subDays(7)->startOfDay();
            $to   = (clone $now)->endOfDay();
        } elseif ($this->filter_type === 'custom') {
            $from = $this->from_date ? $this->from_date->startOfDay() : null;
            $to   = $this->to_date ? $this->to_date->endOfDay() : null;
        } else {
            // default: all time
            $from = null;
            $to   = null;
        }

        return [$from, $to];
    }
}
