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
        Schema::table('stock_adjustment_logs', function (Blueprint $table) {
            $table->foreign(['fabric_id'], 'fabric_id_fk_1')->references(['id'])->on('fabrics')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['product_id'], 'product_id_fk_2')->references(['id'])->on('products')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_adjustment_logs', function (Blueprint $table) {
            $table->dropForeign('fabric_id_fk_1');
            $table->dropForeign('product_id_fk_2');
        });
    }
};
