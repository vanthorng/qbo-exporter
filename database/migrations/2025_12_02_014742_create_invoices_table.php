<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('invoice_number')->unique();  // e.g. INV-2025-0001
            $table->foreignId('customer_id')->nullable();
            // If you don't have a customers table yet, remove `->constrained()` for now

            // Dates
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();

            // Status
            $table->string('status')->default('draft');
            // could be: draft, sent, paid, partial, cancelled, overdue

            // Currency
            $table->string('currency', 3)->default('USD');

            // Money fields (use decimal instead of float)
            $table->decimal('subtotal', 15, 2)->default(0);        // sum of line_total before tax/discount
            $table->decimal('discount_total', 15, 2)->default(0);  // total invoice-level discount
            $table->decimal('tax_total', 15, 2)->default(0);       // total tax for invoice
            $table->decimal('total', 15, 2)->default(0);           // grand total (subtotal - discounts + taxes)
            $table->decimal('paid_total', 15, 2)->default(0);      // how much is paid
            $table->decimal('balance_due', 15, 2)->default(0);     // total - paid_total

            // Optional metadata
            $table->string('reference')->nullable();               // e.g. PO number
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            // Extra flags
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable();     // e.g. monthly, yearly

            $table->timestamps();
            $table->softDeletes(); // if you want soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
