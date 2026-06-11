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
        Schema::table('stock_products', function (Blueprint $table) {
            $table->foreign(['stock_id'], 'stock_products_ibfk_1')->references(['id'])->on('stocks')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['product_id'], 'stock_products_ibfk_2')->references(['id'])->on('products')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_products', function (Blueprint $table) {
            $table->dropForeign('stock_products_ibfk_1');
            $table->dropForeign('stock_products_ibfk_2');
        });
    }
};
