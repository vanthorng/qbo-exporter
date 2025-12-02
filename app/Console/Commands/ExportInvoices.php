<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoicesExport;
use Carbon\Carbon;

class ExportInvoices extends Command
{
    protected $signature = 'invoice:export {--from=} {--to=}';
    protected $description = 'Export invoices to Excel';

    public function handle()
    {
        $from = $this->option('from') ? Carbon::parse($this->option('from'))->startOfDay() : null;
        $to   = $this->option('to') ? Carbon::parse($this->option('to'))->endOfDay() : null;

        $fileName = 'invoices_' . now()->format('Y_m_d_H_i') . '.xlsx';

        Excel::store(
            new \App\Exports\InvoicesExport($from, $to),
            'exports/' . $fileName
        );

        $this->info("Exported: storage/app/exports/{$fileName}");

        return 0;
    }
}
