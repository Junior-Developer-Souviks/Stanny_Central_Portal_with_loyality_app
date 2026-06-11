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
        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreign(['fabric_id'], 'fk_deliveries_fabric')->references(['id'])->on('fabrics')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['product_id'], 'fk_deliveries_product')->references(['id'])->on('products')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['order_id'], 'FK_order_id')->references(['id'])->on('orders')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['order_item_id'], 'FK_order_items_id')->references(['id'])->on('order_items')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['delivered_by'], 'FK_to_delivered_by')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign('fk_deliveries_fabric');
            $table->dropForeign('fk_deliveries_product');
            $table->dropForeign('FK_order_id');
            $table->dropForeign('FK_order_items_id');
            $table->dropForeign('FK_to_delivered_by');
        });
    }
};
