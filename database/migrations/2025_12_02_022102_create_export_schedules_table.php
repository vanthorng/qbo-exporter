<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('export_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Last month invoices"

            // filter type: last_month, last_7_days, custom
            $table->string('filter_type'); 

            // for custom range
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();

            // frequency: every_10_minutes, hourly, daily
            $table->string('frequency')->default('every_10_minutes');

            // is this schedule active?
            $table->boolean('is_active')->default(true);

            // last time it ran
            $table->timestamp('last_run_at')->nullable();

            // last exported file path (optional)
            $table->string('last_file_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_schedules');
    }
};
