<?php

namespace App\Http\Controllers;

use App\Exports\ArrayExport;
use App\Models\QuickBooksToken;
use App\Services\QuickBooksService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuickBooksExportController extends Controller
{
    public function __construct(
        private QuickBooksService $quickBooksService
    ) {}

    /* ==========================================================
     * UI
     * ========================================================== */

    public function showExportForm()
    {
        if (!QuickBooksToken::exists()) {
            return redirect()->route('dashboard')
                ->with('error', 'Please connect QuickBooks first.');
        }

        return view('export_form');
    }

    public function runExport(Request $request)
    {
        $data = $request->validate([
            'export_type' => 'required|in:invoice,vendor,customer,item',
            'format'      => 'required|in:csv,excel',
            'filter_type' => 'nullable|in:none,txn_date,created_time,updated_time',
            'from_date'   => 'nullable|date',
            'to_date'     => 'nullable|date|after_or_equal:from_date',
        ]);

        $filterType = $data['filter_type'] ?? 'none';
        $from       = $data['from_date'] ?? null;
        $to         = $data['to_date'] ?? null;

        return match ($data['export_type']) {
            'invoice'  => $this->exportInvoices($data['format'], $filterType, $from, $to),
            'vendor'   => $this->exportVendors($data['format'], $filterType, $from, $to),
            'customer' => $this->exportCustomers($data['format'], $filterType, $from, $to),
            'item'     => $this->exportItems($data['format'], $filterType, $from, $to),
        };
    }

    /* ==========================================================
     * Helper: build WHERE clause for date filters
     * ========================================================== */

    private function buildDateWhere(string $entity, string $filterType, ?string $from, ?string $to): string
    {
        if ($filterType === 'none' || (!$from && !$to)) {
            return '';
        }

        // Map filter type -> QBO field
        $field = match ($filterType) {
            'txn_date'     => $entity === 'Invoice' ? 'TxnDate' : null,
            'created_time' => 'MetaData.CreateTime',
            'updated_time' => 'MetaData.LastUpdatedTime',
            default        => null,
        };

        if (!$field) {
            return '';
        }

        $conditions = [];
        if ($from) {
            $conditions[] = "$field >= '$from'";
        }
        if ($to) {
            $conditions[] = "$field <= '$to'";
        }

        return $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }

    /* ==========================================================
     * INVOICES
     * ========================================================== */

    private function exportInvoices(string $format, string $filterType, ?string $from, ?string $to)
    {
        $headings = [
            'InvoiceID',
            'DocNumber',
            'CustomerName',
            'TxnDate',
            'DueDate',
            'TotalAmt',
            'Balance',
            'Currency',
        ];

        if ($format === 'csv') {
            return $this->streamEntityCsv('Invoice', $headings, $filterType, $from, $to,
                function ($inv) {
                    return [
                        $inv->Id ?? '',
                        $inv->DocNumber ?? '',
                        $inv->CustomerRef?->name ?? '',
                        $inv->TxnDate ?? '',
                        $inv->DueDate ?? '',
                        $inv->TotalAmt ?? '',
                        $inv->Balance ?? '',
                        $inv->CurrencyRef?->value ?? '',
                    ];
                },
                'qbo_invoices'
            );
        }

        // Excel – build array (good for smaller sets)
        $rows = $this->collectEntityRows('Invoice', $filterType, $from, $to,
            function ($inv) {
                return [
                    $inv->Id ?? '',
                    $inv->DocNumber ?? '',
                    $inv->CustomerRef?->name ?? '',
                    $inv->TxnDate ?? '',
                    $inv->DueDate ?? '',
                    $inv->TotalAmt ?? '',
                    $inv->Balance ?? '',
                    $inv->CurrencyRef?->value ?? '',
                ];
            }
        );

        $fileName = 'qbo_invoices_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ArrayExport($rows, $headings), $fileName);
    }

    /* ==========================================================
     * VENDORS
     * ========================================================== */

    private function exportVendors(string $format, string $filterType, ?string $from, ?string $to)
    {
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

        if ($format === 'csv') {
            return $this->streamEntityCsv('Vendor', $headings, $filterType, $from, $to,
                function ($v) {
                    return [
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
                },
                'qbo_vendors'
            );
        }

        $rows = $this->collectEntityRows('Vendor', $filterType, $from, $to,
            function ($v) {
                return [
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
        );

        $fileName = 'qbo_vendors_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ArrayExport($rows, $headings), $fileName);
    }

    /* ==========================================================
     * CUSTOMERS
     * ========================================================== */

    private function exportCustomers(string $format, string $filterType, ?string $from, ?string $to)
    {
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

        if ($format === 'csv') {
            return $this->streamEntityCsv('Customer', $headings, $filterType, $from, $to,
                function ($c) {
                    return [
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
                },
                'qbo_customers'
            );
        }

        $rows = $this->collectEntityRows('Customer', $filterType, $from, $to,
            function ($c) {
                return [
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
        );

        $fileName = 'qbo_customers_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ArrayExport($rows, $headings), $fileName);
    }

    /* ==========================================================
     * ITEMS
     * ========================================================== */

    private function exportItems(string $format, string $filterType, ?string $from, ?string $to)
    {
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

        if ($format === 'csv') {
            return $this->streamEntityCsv('Item', $headings, $filterType, $from, $to,
                function ($i) {
                    return [
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
                },
                'qbo_items'
            );
        }

        $rows = $this->collectEntityRows('Item', $filterType, $from, $to,
            function ($i) {
                return [
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
        );

        $fileName = 'qbo_items_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ArrayExport($rows, $headings), $fileName);
    }

    /* ==========================================================
     * Generic helpers (stream CSV / collect rows)
     * ========================================================== */

    private function streamEntityCsv(
        string $entity,
        array $headings,
        string $filterType,
        ?string $from,
        ?string $to,
        callable $rowMapper,
        string $filePrefix
    ): StreamedResponse {
        $fileName = $filePrefix . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use (
            $entity, $headings, $filterType, $from, $to, $rowMapper
        ) {
            @set_time_limit(0);
            @ini_set('memory_limit', '512M');
            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }
            @ini_set('zlib.output_compression', '0');
            @ini_set('output_buffering', 'off');
            @ini_set('implicit_flush', '1');
            ob_implicit_flush(true);
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $dataService = $this->quickBooksService->getDataService();
            $handle      = fopen('php://output', 'w');

            fputcsv($handle, $headings);

            $startPosition = 1;
            $pageSize      = 500;
            $where         = $this->buildDateWhere($entity, $filterType, $from, $to);

            do {
                $query = sprintf(
                    "SELECT * FROM %s %s STARTPOSITION %d MAXRESULTS %d",
                    $entity,
                    $where,
                    $startPosition,
                    $pageSize
                );

                $entities = $dataService->Query($query);

                if ($entities === null || empty($entities)) {
                    break;
                }

                foreach ($entities as $e) {
                    fputcsv($handle, $rowMapper($e));
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

    private function collectEntityRows(
        string $entity,
        string $filterType,
        ?string $from,
        ?string $to,
        callable $rowMapper
    ): array {
        $dataService = $this->quickBooksService->getDataService();
        $rows        = [];
        $startPos    = 1;
        $pageSize    = 500;
        $where       = $this->buildDateWhere($entity, $filterType, $from, $to);

        do {
            $query    = sprintf(
                "SELECT * FROM %s %s STARTPOSITION %d MAXRESULTS %d",
                $entity,
                $where,
                $startPos,
                $pageSize
            );
            $entities = $dataService->Query($query);

            if ($entities === null || empty($entities)) {
                break;
            }

            foreach ($entities as $e) {
                $rows[] = $rowMapper($e);
            }

            $count    = count($entities);
            $startPos += $count;
        } while ($count === $pageSize);

        return $rows;
    }
}
