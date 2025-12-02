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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            // Optional link to a products table
            $table->foreignId('product_id')->nullable();
            // If no products table yet, remove `->constrained()`

            // Line details
            $table->string('name');              // item name / product name
            $table->text('description')->nullable();

            // qty & pricing
            $table->decimal('quantity', 15, 4)->default(1);    // supports 0.5 etc
            $table->string('unit')->nullable();                // pcs, hours, kg...

            $table->decimal('unit_price', 15, 4)->default(0);  // price per unit

            // Discounts & tax at line level
            $table->decimal('discount_amount', 15, 4)->default(0); // absolute discount
            $table->decimal('discount_percent', 5, 2)->default(0); // % discount (optional)
            $table->decimal('tax_rate', 5, 2)->default(0);         // e.g. 10.00 = 10%

            // Calculated line total (for faster reports)
            // Example: (quantity * unit_price) - discount + tax
            $table->decimal('line_total', 15, 4)->default(0);

            // For ordering the lines
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
