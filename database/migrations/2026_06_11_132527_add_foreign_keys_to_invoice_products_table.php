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
        Schema::table('invoice_products', function (Blueprint $table) {
            $table->foreign(['invoice_id'], 'invoice_products_ibfk_1')->references(['id'])->on('invoices')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['product_id'], 'invoice_products_ibfk_3')->references(['id'])->on('products')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_products', function (Blueprint $table) {
            $table->dropForeign('invoice_products_ibfk_1');
            $table->dropForeign('invoice_products_ibfk_3');
        });
    }
};
