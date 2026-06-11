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
        Schema::table('fabrics', function (Blueprint $table) {
            $table->foreign(['collection_id'], 'collection_id_fk')->references(['id'])->on('collections')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['fabric_category_id'], 'fabric_category_id_fk')->references(['id'])->on('fabric_categories')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fabrics', function (Blueprint $table) {
            $table->dropForeign('collection_id_fk');
            $table->dropForeign('fabric_category_id_fk');
        });
    }
};
