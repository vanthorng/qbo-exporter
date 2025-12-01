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
     * Export Vendors as streamed CSV (handles large datasets).
     */
    public function exportVendors(): StreamedResponse
    {
        if (!QuickBooksToken::exists()) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Please connect QuickBooks first.');
        }

        $fileName = 'qbo_vendors_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () {
            $dataService = $this->quickBooksService->getDataService();

            $handle = fopen('php://output', 'w');

            // CSV header for Vendor data
            fputcsv($handle, [
                'VendorID',
                'VendorName',
                'Email',
                'Phone',
                'Address',
                'City',
                'State',
                'PostalCode',
                'Country',
            ]);

            $startPosition = 1;
            $pageSize      = 500; // tune for performance

            do {
                $query = sprintf(
                    "SELECT * FROM Vendor STARTPOSITION %d MAXRESULTS %d",
                    $startPosition,
                    $pageSize
                );

                $vendors = $dataService->Query($query);
                $error   = $dataService->getLastError();

                if ($error) {
                    // log error and stop
                    break;
                }

                if (empty($vendors)) {
                    break;
                }

                foreach ($vendors as $vendor) {
                    fputcsv($handle, [
                        $vendor->Id ?? '',
                        $vendor->DisplayName ?? '',
                        $vendor->PrimaryEmailAddr?->Address ?? '',
                        $vendor->PrimaryPhone?->FreeFormNumber ?? '',
                        $vendor->BillAddr?->Line1 ?? '',
                        $vendor->BillAddr?->City ?? '',
                        $vendor->BillAddr?->CountrySubDivisionCode ?? '',
                        $vendor->BillAddr?->PostalCode ?? '',
                        $vendor->BillAddr?->Country ?? '',
                    ]);
                }

                $count          = count($vendors);
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
