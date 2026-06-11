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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('supplier_id')->index('purchase_orders_supplier_id_foreign');
            $table->string('unique_id')->unique();
            $table->string('product_ids')->nullable();
            $table->json('fabric_ids')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('pin')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('landmark')->nullable();
            $table->decimal('total_price', 15);
            $table->boolean('is_good_in')->default(false);
            $table->enum('goods_in_type', ['scan', 'bulk', 'opening_stock'])->nullable();
            $table->boolean('status')->default(false);
            $table->tinyInteger('is_approved')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
