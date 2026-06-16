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
        Schema::create('stock_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('stock_id')->index('stock_products_stock_id_foreign');
            $table->unsignedBigInteger('product_id')->index('stock_products_product_id_foreign');
            $table->integer('qty_in_pieces');
            $table->decimal('qty_while_grn', 10)->nullable();
            $table->decimal('piece_price', 10);
            $table->decimal('total_price', 15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_products');
    }
};
