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
        Schema::create('proforma_invoice_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('proforma_id')->nullable()->index('proforma_id_fk_proforma_invoices');
            $table->integer('product_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('unit_price', 10)->nullable();
            $table->decimal('total_price', 12)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_invoice_items');
    }
};
