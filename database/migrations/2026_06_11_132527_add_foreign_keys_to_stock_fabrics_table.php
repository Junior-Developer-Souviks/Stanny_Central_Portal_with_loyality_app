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
        Schema::table('stock_fabrics', function (Blueprint $table) {
            $table->foreign(['fabric_id'], 'fabric_id_ibfk')->references(['id'])->on('fabrics')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['stock_id'], 'stock_id_fk')->references(['id'])->on('stocks')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_fabrics', function (Blueprint $table) {
            $table->dropForeign('fabric_id_ibfk');
            $table->dropForeign('stock_id_fk');
        });
    }
};
