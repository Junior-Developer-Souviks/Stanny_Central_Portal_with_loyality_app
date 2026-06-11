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
        Schema::table('products', function (Blueprint $table) {
            $table->foreign(['category_id'], 'category_id_fk')->references(['id'])->on('categories')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['collection_id'], 'collection_id_foreign_key')->references(['id'])->on('collections')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('category_id_fk');
            $table->dropForeign('collection_id_foreign_key');
        });
    }
};
