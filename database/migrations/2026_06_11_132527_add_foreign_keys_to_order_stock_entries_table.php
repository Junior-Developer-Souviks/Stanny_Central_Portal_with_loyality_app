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
        Schema::table('order_stock_entries', function (Blueprint $table) {
            $table->foreign(['fabric_id'], 'order_stock_entries_ibfk_1')->references(['id'])->on('fabrics')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['product_id'], 'order_stock_entries_ibfk_2')->references(['id'])->on('products')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['created_by'], 'order_stock_entries_ibfk_3')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_stock_entries', function (Blueprint $table) {
            $table->dropForeign('order_stock_entries_ibfk_1');
            $table->dropForeign('order_stock_entries_ibfk_2');
            $table->dropForeign('order_stock_entries_ibfk_3');
        });
    }
};
