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
        Schema::create('catalogue_page_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('catalogue_id')->index('catalogue_page_items_catalogue_id_foreign');
            $table->unsignedBigInteger('page_id')->index('catalogue_page_items_page_id_foreign');
            $table->string('catalog_item');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogue_page_items');
    }
};
