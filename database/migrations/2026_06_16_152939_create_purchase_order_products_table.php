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
        Schema::create('purchase_order_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('purchase_order_id')->index('purchase_order_products_purchase_order_id_foreign');
            $table->unsignedBigInteger('collection_id')->nullable()->index('purchase_order_products_collection_id_foreign');
            $table->enum('stock_type', ['fabric', 'product']);
            $table->decimal('piece_price', 15);
            $table->decimal('total_price', 15);
            $table->unsignedBigInteger('fabric_id')->nullable()->index('purchase_order_products_fabric_id_foreign');
            $table->string('fabric_name')->nullable();
            $table->decimal('qty_in_meter', 15)->nullable();
            $table->decimal('qty_while_grn_fabric', 10)->nullable();
            $table->unsignedBigInteger('product_id')->nullable()->index('purchase_order_products_product_id_foreign');
            $table->string('product_name')->nullable();
            $table->integer('qty_in_pieces')->nullable();
            $table->decimal('qty_while_grn_product', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_products');
    }
};
