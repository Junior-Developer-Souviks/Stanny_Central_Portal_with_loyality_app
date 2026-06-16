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
        Schema::create('stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('grn_no')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->index('stocks_purchase_order_id_foreign');
            $table->string('po_unique_id')->nullable();
            $table->unsignedBigInteger('return_id')->nullable();
            $table->string('return_order_no')->nullable();
            $table->string('goods_in_type')->nullable();
            $table->string('product_ids')->nullable();
            $table->json('fabric_ids')->nullable();
            $table->decimal('total_price', 15)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
