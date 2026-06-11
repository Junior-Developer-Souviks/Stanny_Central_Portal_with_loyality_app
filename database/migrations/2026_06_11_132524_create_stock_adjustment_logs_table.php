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
        Schema::create('stock_adjustment_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('fabric_id')->nullable()->index('fabric_id_fk_1');
            $table->unsignedBigInteger('product_id')->nullable()->index('product_id_fk_2');
            $table->decimal('adjustment', 15)->nullable();
            $table->decimal('old_qty', 15)->nullable();
            $table->decimal('new_qty', 15)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_logs');
    }
};
