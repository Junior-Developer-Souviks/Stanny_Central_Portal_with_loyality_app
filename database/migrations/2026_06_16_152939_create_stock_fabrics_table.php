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
        Schema::create('stock_fabrics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('stock_id')->index('stock_fabrics_stock_id_foreign');
            $table->unsignedBigInteger('fabric_id')->index('stock_fabrics_fabric_id_foreign');
            $table->decimal('qty_in_meter', 10);
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
        Schema::dropIfExists('stock_fabrics');
    }
};
