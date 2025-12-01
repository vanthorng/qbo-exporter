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
Route::get('/quickbooks/export/vendors', [QuickBooksExportController::class, 'exportVendors'])
    ->name('qbo.export.vendors');
