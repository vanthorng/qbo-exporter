<?php

use App\Http\Controllers\QuickBooksAuthController;
use App\Http\Controllers\QuickBooksExportController;
use Illuminate\Support\Facades\Route;

// Simple dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::view('/dashboard', 'dashboard')->name('dashboard');

// Connect / OAuth
Route::get('/quickbooks/connect', [QuickBooksAuthController::class, 'connect'])
    ->name('qbo.connect');

Route::get('/quickbooks/callback', [QuickBooksAuthController::class, 'callback'])
    ->name('qbo.callback');

// Export
// existing:
Route::get('/quickbooks/export/invoices', [QuickBooksExportController::class, 'exportInvoices'])
    ->name('qbo.export.invoices');

// Vendors
Route::get('/quickbooks/export/vendors/csv',   [QuickBooksExportController::class, 'exportVendorsCsv'])
    ->name('qbo.export.vendors.csv');
Route::get('/quickbooks/export/vendors/excel', [QuickBooksExportController::class, 'exportVendorsExcel'])
    ->name('qbo.export.vendors.excel');

// Customers
Route::get('/quickbooks/export/customers/csv',   [QuickBooksExportController::class, 'exportCustomersCsv'])
    ->name('qbo.export.customers.csv');
Route::get('/quickbooks/export/customers/excel', [QuickBooksExportController::class, 'exportCustomersExcel'])
    ->name('qbo.export.customers.excel');

// Items
Route::get('/quickbooks/export/items/csv',   [QuickBooksExportController::class, 'exportItemsCsv'])
    ->name('qbo.export.items.csv');
Route::get('/quickbooks/export/items/excel', [QuickBooksExportController::class, 'exportItemsExcel'])
    ->name('qbo.export.items.excel');