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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('order_id')->index('fk_order_id');
            $table->unsignedBigInteger('order_item_id')->index('fk_order_items_id');
            $table->unsignedBigInteger('product_id')->nullable()->index('fk_deliveries_product');
            $table->unsignedBigInteger('fabric_id')->nullable()->index('fk_deliveries_fabric');
            $table->integer('fabric_quantity')->nullable();
            $table->integer('delivered_quantity')->comment('Actual Quantity Delivered');
            $table->string('unit');
            $table->enum('status', ['Pending', 'Delivered', 'Alteration Required', 'Rejected', 'Received by Sales Team'])->default('Pending');
            $table->unsignedBigInteger('delivered_by')->index('fk_to_delivered_by');
            $table->integer('customer_delivered_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('received_at_salesman')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
