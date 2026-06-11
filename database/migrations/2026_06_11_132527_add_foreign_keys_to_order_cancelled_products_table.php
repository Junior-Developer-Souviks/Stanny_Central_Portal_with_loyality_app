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
        Schema::table('order_cancelled_products', function (Blueprint $table) {
            $table->foreign(['order_id'], 'orders_ibfk_1')->references(['id'])->on('orders')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['product_id'], 'orders_ibfk_2')->references(['id'])->on('products')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_cancelled_products', function (Blueprint $table) {
            $table->dropForeign('orders_ibfk_1');
            $table->dropForeign('orders_ibfk_2');
        });
    }
};
