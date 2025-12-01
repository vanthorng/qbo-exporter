<?php

namespace App\Http\Controllers;

use App\Models\QuickBooksToken;
use App\Services\QuickBooksService;
use App\Exports\ArrayExport;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Maatwebsite\Excel\Facades\Excel;

class QuickBooksExportController extends Controller
{
    public function __construct(
        private QuickBooksService $quickBooksService
    ) {}


    /* ==========================================================
     * INVOICES  (CSV STREAM – ALREADY IMPLEMENTED)
     * ========================================================== */

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
            $handle      = fopen('php://output', 'w');

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
            $pageSize      = 500;

            do {
                $query    = sprintf("SELECT * FROM Invoice STARTPOSITION %d MAXRESULTS %d",
                                    $startPosition, $pageSize);
                $entities = $dataService->Query($query);
                $error    = $dataService->getLastError();

                if ($error || empty($entities)) {
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
                flush();
            } while ($count === $pageSize);

            fclose($handle);
        }, $fileName, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }


    /* ==========================================================
     * VENDORS
     * ========================================================== */

    /** CSV (streamed, big datasets) */
    public function exportVendorsCsv(): StreamedResponse
    {
        if (!QuickBooksToken::exists()) {
            return redirect()->route('dashboard')
                ->with('error', 'Please connect QuickBooks first.');
        }

        $fileName = 'qbo_vendors_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () {
            $dataService = $this->quickBooksService->getDataService();
            $handle      = fopen('php://output', 'w');

            // More detailed fields: billing address, terms, etc.
            fputcsv($handle, [
                'VendorID',
                'DisplayName',
                'CompanyName',
                'GivenName',
                'FamilyName',
                'PrimaryEmail',
                'PrimaryPhone',
                'MobilePhone',
                'Fax',
                'Bill_Line1',
                'Bill_Line2',
                'Bill_City',
                'Bill_State',
                'Bill_PostalCode',
                'Bill_Country',
                'TermsName',
                'TermsRef',
                'Active',
                'TaxIdentifier',
            ]);

            $startPosition = 1;
            $pageSize      = 500;

            do {
                $query   = sprintf("SELECT * FROM Vendor STARTPOSITION %d MAXRESULTS %d",
                                   $startPosition, $pageSize);
                $vendors = $dataService->Query($query);
                $error   = $dataService->getLastError();

                if ($error || empty($vendors)) {
                    break;
                }

                foreach ($vendors as $v) {
                    fputcsv($handle, [
                        $v->Id ?? '',
                        $v->DisplayName ?? '',
                        $v->CompanyName ?? '',
                        $v->GivenName ?? '',
                        $v->FamilyName ?? '',
                        $v->PrimaryEmailAddr?->Address ?? '',
                        $v->PrimaryPhone?->FreeFormNumber ?? '',
                        $v->Mobile?->FreeFormNumber ?? '',
                        $v->Fax?->FreeFormNumber ?? '',
                        $v->BillAddr?->Line1 ?? '',
                        $v->BillAddr?->Line2 ?? '',
                        $v->BillAddr?->City ?? '',
                        $v->BillAddr?->CountrySubDivisionCode ?? '',
                        $v->BillAddr?->PostalCode ?? '',
                        $v->BillAddr?->Country ?? '',
                        $v->TermRef?->name ?? '',
                        $v->TermRef?->value ?? '',
                        isset($v->Active) ? ($v->Active ? 'true' : 'false') : '',
                        $v->TaxIdentifier ?? '',
                    ]);
                }

                $count          = count($vendors);
                $startPosition += $count;
                flush();
            } while ($count === $pageSize);

            fclose($handle);
        }, $fileName, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    /** Excel (good for moderate datasets; Excel row limit ~1,048,576) */
    public function exportVendorsExcel()
    {
        if (!QuickBooksToken::exists()) {
            return redirect()->route('dashboard')
                ->with('error', 'Please connect QuickBooks first.');
        }

        $dataService = $this->quickBooksService->getDataService();

        $headings = [
            'VendorID',
            'DisplayName',
            'CompanyName',
            'GivenName',
            'FamilyName',
            'PrimaryEmail',
            'PrimaryPhone',
            'MobilePhone',
            'Fax',
            'Bill_Line1',
            'Bill_Line2',
            'Bill_City',
            'Bill_State',
            'Bill_PostalCode',
            'Bill_Country',
            'TermsName',
            'TermsRef',
            'Active',
            'TaxIdentifier',
        ];

        $rows          = [];
        $startPosition = 1;
        $pageSize      = 500;

        do {
            $query   = sprintf("SELECT * FROM Vendor STARTPOSITION %d MAXRESULTS %d",
                               $startPosition, $pageSize);
            $vendors = $dataService->Query($query);
            $error   = $dataService->getLastError();

            if ($error || empty($vendors)) {
                break;
            }

            foreach ($vendors as $v) {
                $rows[] = [
                    $v->Id ?? '',
                    $v->DisplayName ?? '',
                    $v->CompanyName ?? '',
                    $v->GivenName ?? '',
                    $v->FamilyName ?? '',
                    $v->PrimaryEmailAddr?->Address ?? '',
                    $v->PrimaryPhone?->FreeFormNumber ?? '',
                    $v->Mobile?->FreeFormNumber ?? '',
                    $v->Fax?->FreeFormNumber ?? '',
                    $v->BillAddr?->Line1 ?? '',
                    $v->BillAddr?->Line2 ?? '',
                    $v->BillAddr?->City ?? '',
                    $v->BillAddr?->CountrySubDivisionCode ?? '',
                    $v->BillAddr?->PostalCode ?? '',
                    $v->BillAddr?->Country ?? '',
                    $v->TermRef?->name ?? '',
                    $v->TermRef?->value ?? '',
                    isset($v->Active) ? ($v->Active ? 'true' : 'false') : '',
                    $v->TaxIdentifier ?? '',
                ];
            }

            $count          = count($vendors);
            $startPosition += $count;
        } while ($count === $pageSize);

        $fileName = 'qbo_vendors_' . now()->format('Ymd_His') . '.xlsx';

        // NOTE: For near-million rows this will be heavy; for huge exports prefer CSV.
        return Excel::download(new ArrayExport($rows, $headings), $fileName);
    }


    /* ==========================================================
     * CUSTOMERS
     * ========================================================== */

    public function exportCustomersCsv(): StreamedResponse
    {
        if (!QuickBooksToken::exists()) {
            return redirect()->route('dashboard')
                ->with('error', 'Please connect QuickBooks first.');
        }

        $fileName = 'qbo_customers_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () {
            $dataService = $this->quickBooksService->getDataService();
            $handle      = fopen('php://output', 'w');

            fputcsv($handle, [
                'CustomerID',
                'DisplayName',
                'CompanyName',
                'GivenName',
                'FamilyName',
                'PrimaryEmail',
                'PrimaryPhone',
                'MobilePhone',
                'Bill_Line1',
                'Bill_City',
                'Bill_State',
                'Bill_PostalCode',
                'Bill_Country',
                'Ship_Line1',
                'Ship_City',
                'Ship_State',
                'Ship_PostalCode',
                'Ship_Country',
                'TermsName',
                'TermsRef',
                'Balance',
                'Currency',
            ]);

            $startPosition = 1;
            $pageSize      = 500;

            do {
                $query     = sprintf("SELECT * FROM Customer STARTPOSITION %d MAXRESULTS %d",
                                     $startPosition, $pageSize);
                $customers = $dataService->Query($query);
                $error     = $dataService->getLastError();

                if ($error || empty($customers)) {
                    break;
                }

                foreach ($customers as $c) {
                    fputcsv($handle, [
                        $c->Id ?? '',
                        $c->DisplayName ?? '',
                        $c->CompanyName ?? '',
                        $c->GivenName ?? '',
                        $c->FamilyName ?? '',
                        $c->PrimaryEmailAddr?->Address ?? '',
                        $c->PrimaryPhone?->FreeFormNumber ?? '',
                        $c->Mobile?->FreeFormNumber ?? '',
                        $c->BillAddr?->Line1 ?? '',
                        $c->BillAddr?->City ?? '',
                        $c->BillAddr?->CountrySubDivisionCode ?? '',
                        $c->BillAddr?->PostalCode ?? '',
                        $c->BillAddr?->Country ?? '',
                        $c->ShipAddr?->Line1 ?? '',
                        $c->ShipAddr?->City ?? '',
                        $c->ShipAddr?->CountrySubDivisionCode ?? '',
                        $c->ShipAddr?->PostalCode ?? '',
                        $c->ShipAddr?->Country ?? '',
                        $c->SalesTermRef?->name ?? '',
                        $c->SalesTermRef?->value ?? '',
                        $c->Balance ?? '',
                        $c->CurrencyRef?->value ?? '',
                    ]);
                }

                $count          = count($customers);
                $startPosition += $count;
                flush();
            } while ($count === $pageSize);

            fclose($handle);
        }, $fileName, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    public function exportCustomersExcel()
    {
        if (!QuickBooksToken::exists()) {
            return redirect()->route('dashboard')
                ->with('error', 'Please connect QuickBooks first.');
        }

        $dataService = $this->quickBooksService->getDataService();

        $headings = [
            'CustomerID',
            'DisplayName',
            'CompanyName',
            'GivenName',
            'FamilyName',
            'PrimaryEmail',
            'PrimaryPhone',
            'MobilePhone',
            'Bill_Line1',
            'Bill_City',
            'Bill_State',
            'Bill_PostalCode',
            'Bill_Country',
            'Ship_Line1',
            'Ship_City',
            'Ship_State',
            'Ship_PostalCode',
            'Ship_Country',
            'TermsName',
            'TermsRef',
            'Balance',
            'Currency',
        ];

        $rows          = [];
        $startPosition = 1;
        $pageSize      = 500;

        do {
            $query     = sprintf("SELECT * FROM Customer STARTPOSITION %d MAXRESULTS %d",
                                 $startPosition, $pageSize);
            $customers = $dataService->Query($query);
            $error     = $dataService->getLastError();

            if ($error || empty($customers)) {
                break;
            }

            foreach ($customers as $c) {
                $rows[] = [
                    $c->Id ?? '',
                    $c->DisplayName ?? '',
                    $c->CompanyName ?? '',
                    $c->GivenName ?? '',
                    $c->FamilyName ?? '',
                    $c->PrimaryEmailAddr?->Address ?? '',
                    $c->PrimaryPhone?->FreeFormNumber ?? '',
                    $c->Mobile?->FreeFormNumber ?? '',
                    $c->BillAddr?->Line1 ?? '',
                    $c->BillAddr?->City ?? '',
                    $c->BillAddr?->CountrySubDivisionCode ?? '',
                    $c->BillAddr?->PostalCode ?? '',
                    $c->BillAddr?->Country ?? '',
                    $c->ShipAddr?->Line1 ?? '',
                    $c->ShipAddr?->City ?? '',
                    $c->ShipAddr?->CountrySubDivisionCode ?? '',
                    $c->ShipAddr?->PostalCode ?? '',
                    $c->ShipAddr?->Country ?? '',
                    $c->SalesTermRef?->name ?? '',
                    $c->SalesTermRef?->value ?? '',
                    $c->Balance ?? '',
                    $c->CurrencyRef?->value ?? '',
                ];
            }

            $count          = count($customers);
            $startPosition += $count;
        } while ($count === $pageSize);

        $fileName = 'qbo_customers_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ArrayExport($rows, $headings), $fileName);
    }


    /* ==========================================================
     * ITEMS
     * ========================================================== */

    public function exportItemsCsv(): StreamedResponse
    {
        if (!QuickBooksToken::exists()) {
            return redirect()->route('dashboard')
                ->with('error', 'Please connect QuickBooks first.');
        }

        $fileName = 'qbo_items_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () {
            $dataService = $this->quickBooksService->getDataService();
            $handle      = fopen('php://output', 'w');

            fputcsv($handle, [
                'ItemID',
                'Name',
                'Sku',
                'Type',
                'Description',
                'Active',
                'IncomeAccountName',
                'ExpenseAccountName',
                'AssetAccountName',
                'UnitPrice',
                'PurchaseCost',
                'Taxable',
            ]);

            $startPosition = 1;
            $pageSize      = 500;

            do {
                $query = sprintf("SELECT * FROM Item STARTPOSITION %d MAXRESULTS %d",
                                 $startPosition, $pageSize);
                $items = $dataService->Query($query);
                $error = $dataService->getLastError();

                if ($error || empty($items)) {
                    break;
                }

                foreach ($items as $i) {
                    fputcsv($handle, [
                        $i->Id ?? '',
                        $i->Name ?? '',
                        $i->Sku ?? '',
                        $i->Type ?? '',
                        $i->Description ?? '',
                        isset($i->Active) ? ($i->Active ? 'true' : 'false') : '',
                        $i->IncomeAccountRef?->name ?? '',
                        $i->ExpenseAccountRef?->name ?? '',
                        $i->AssetAccountRef?->name ?? '',
                        $i->UnitPrice ?? '',
                        $i->PurchaseCost ?? '',
                        isset($i->Taxable) ? ($i->Taxable ? 'true' : 'false') : '',
                    ]);
                }

                $count          = count($items);
                $startPosition += $count;
                flush();
            } while ($count === $pageSize);

            fclose($handle);
        }, $fileName, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    public function exportItemsExcel()
    {
        if (!QuickBooksToken::exists()) {
            return redirect()->route('dashboard')
                ->with('error', 'Please connect QuickBooks first.');
        }

        $dataService = $this->quickBooksService->getDataService();

        $headings = [
            'ItemID',
            'Name',
            'Sku',
            'Type',
            'Description',
            'Active',
            'IncomeAccountName',
            'ExpenseAccountName',
            'AssetAccountName',
            'UnitPrice',
            'PurchaseCost',
            'Taxable',
        ];

        $rows          = [];
        $startPosition = 1;
        $pageSize      = 500;

        do {
            $query = sprintf("SELECT * FROM Item STARTPOSITION %d MAXRESULTS %d",
                             $startPosition, $pageSize);
            $items = $dataService->Query($query);
            $error = $dataService->getLastError();

            if ($error || empty($items)) {
                break;
            }

            foreach ($items as $i) {
                $rows[] = [
                    $i->Id ?? '',
                    $i->Name ?? '',
                    $i->Sku ?? '',
                    $i->Type ?? '',
                    $i->Description ?? '',
                    isset($i->Active) ? ($i->Active ? 'true' : 'false') : '',
                    $i->IncomeAccountRef?->name ?? '',
                    $i->ExpenseAccountRef?->name ?? '',
                    $i->AssetAccountRef?->name ?? '',
                    $i->UnitPrice ?? '',
                    $i->PurchaseCost ?? '',
                    isset($i->Taxable) ? ($i->Taxable ? 'true' : 'false') : '',
                ];
            }

            $count          = count($items);
            $startPosition += $count;
        } while ($count === $pageSize);

        $fileName = 'qbo_items_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ArrayExport($rows, $headings), $fileName);
    }
}
