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
        Schema::table('catalogue_page_items', function (Blueprint $table) {
            $table->foreign(['catalogue_id'])->references(['id'])->on('catalogues')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['page_id'])->references(['id'])->on('pages')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogue_page_items', function (Blueprint $table) {
            $table->dropForeign('catalogue_page_items_catalogue_id_foreign');
            $table->dropForeign('catalogue_page_items_page_id_foreign');
        });
    }
};
