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
        Schema::create('invoice_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('order_item_id');
            $table->string('product_name')->nullable();
            $table->integer('quantity')->nullable();
            $table->double('single_product_price')->comment('order_product_piece_price');
            $table->double('total_price');
            $table->tinyInteger('is_store_address_outstation')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_products');
    }
};
