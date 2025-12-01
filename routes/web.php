<?php

use App\Http\Controllers\QuickBooksAuthController;
use App\Http\Controllers\QuickBooksExportController;
use Illuminate\Support\Facades\Route;

// Simple dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    $isConnected = \App\Models\QuickBooksToken::exists();

    return view('dashboard', [
        'isConnected' => $isConnected,
    ]);
})->name('dashboard');

// Connect / OAuth
Route::get('/quickbooks/connect', [QuickBooksAuthController::class, 'connect'])
    ->name('qbo.connect');

Route::get('/quickbooks/callback', [QuickBooksAuthController::class, 'callback'])
    ->name('qbo.callback');

// Export
// Export dashboard (form)
Route::get('/quickbooks/export', [QuickBooksExportController::class, 'showExportForm'])
    ->name('qbo.export.form');

// Handle export with filters
Route::post('/quickbooks/export/run', [QuickBooksExportController::class, 'runExport'])
    ->name('qbo.export.run');
