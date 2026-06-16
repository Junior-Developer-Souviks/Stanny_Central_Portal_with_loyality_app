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
        Schema::create('order_stock_entries', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('order_id')->nullable()->index('fk_to_orders')->comment('from_orders_table');
            $table->unsignedBigInteger('order_item_id')->nullable()->index('fk_to_order_item_id')->comment('from_order_items_table');
            $table->unsignedBigInteger('product_id')->nullable()->index('fk_to_product_id');
            $table->unsignedBigInteger('fabric_id')->nullable()->index('fk_to_fabric_id');
            $table->decimal('quantity', 10)->nullable();
            $table->decimal('extra_meter')->nullable()->default(0);
            $table->string('unit')->nullable()->comment('meters/pcs');
            $table->decimal('previous_quantity')->default(0);
            $table->unsignedBigInteger('created_by')->index('fk_to_created_by')->comment('from_user_table');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_stock_entries');
    }
};
