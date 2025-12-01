<?php

namespace App\Http\Controllers;

use App\Models\QuickBooksToken;
use App\Services\QuickBooksService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuickBooksExportController extends Controller
{
    public function __construct(
        private QuickBooksService $quickBooksService
    ) {}

    /**
     * Export invoices as streamed CSV (handles large datasets).
     */
    public function exportInvoices(): StreamedResponse
    {
        if (!QuickBooksToken::exists()) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Please connect QuickBooks first.');
        }

        $fileName = 'qbo_invoices_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () {
            $dataService = $this->quickBooksService->getDataService();

            $handle = fopen('php://output', 'w');

            // CSV header
            fputcsv($handle, [
                'InvoiceID',
                'DocNumber',
                'CustomerName',
                'TxnDate',
                'DueDate',
                'TotalAmt',
                'Balance',
                'Currency',
            ]);

            $startPosition = 1;
            $pageSize      = 500; // tune for performance

            do {
                $query = sprintf(
                    "SELECT * FROM Invoice STARTPOSITION %d MAXRESULTS %d",
                    $startPosition,
                    $pageSize
                );

                $entities = $dataService->Query($query);
                $error    = $dataService->getLastError();

                if ($error) {
                    // log error and stop
                    // logger()->error('QBO query error: '.$error->getResponseBody());
                    break;
                }

                if (empty($entities)) {
                    break;
                }

                foreach ($entities as $inv) {
                    fputcsv($handle, [
                        $inv->Id ?? '',
                        $inv->DocNumber ?? '',
                        $inv->CustomerRef?->name ?? '',
                        $inv->TxnDate ?? '',
                        $inv->DueDate ?? '',
                        $inv->TotalAmt ?? '',
                        $inv->Balance ?? '',
                        $inv->CurrencyRef?->value ?? '',
                    ]);
                }

                $count          = count($entities);
                $startPosition += $count;

                // push data to the browser for huge exports
                flush();
            } while ($count === $pageSize);

            fclose($handle);
        }, $fileName, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }
}
