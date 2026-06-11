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
        Schema::table('product_fabrics', function (Blueprint $table) {
            $table->foreign(['fabric_id'], 'fabric_id_foreign_key')->references(['id'])->on('fabrics')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['product_id'], 'product_id_foreign_key')->references(['id'])->on('products')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_fabrics', function (Blueprint $table) {
            $table->dropForeign('fabric_id_foreign_key');
            $table->dropForeign('product_id_foreign_key');
        });
    }
};
