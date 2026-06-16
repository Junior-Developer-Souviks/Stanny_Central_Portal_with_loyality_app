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
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('collection_id')->nullable()->index('products_collection_id_foreign');
            $table->unsignedBigInteger('category_id')->index('products_category_id_foreign');
            $table->unsignedBigInteger('sub_category_id')->nullable()->index('products_sub_category_id_foreign');
            $table->string('name');
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('product_code')->nullable();
            $table->decimal('gst_details', 5)->nullable()->default(0);
            $table->string('product_image')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Inactive');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
