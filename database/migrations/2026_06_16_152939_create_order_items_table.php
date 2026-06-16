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
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('catalogue_id')->nullable();
            $table->string('cat_page_number')->nullable();
            $table->string('cat_page_item')->nullable();
            $table->unsignedBigInteger('order_id')->index('order_items_order_id_foreign');
            $table->unsignedBigInteger('product_id')->nullable()->index('order_items_product_id_foreign');
            $table->string('collection')->nullable()->index('order_items_collection_type_foreign');
            $table->string('fabrics')->nullable();
            $table->enum('fittings', ['Regular Fit', 'Slim Fit', 'Loose Fit'])->nullable();
            $table->enum('priority_level', ['Priority', 'Non Priority'])->nullable();
            $table->string('expected_delivery_date')->nullable();
            $table->integer('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('piece_price')->nullable();
            $table->string('product_name')->nullable();
            $table->decimal('total_price', 10);
            $table->string('shoulder_type', 50)->nullable();
            $table->string('mens_hand_stitching')->nullable();
            $table->string('ladies_hand_stitching')->nullable();
            $table->string('vents', 50)->nullable();
            $table->enum('vents_required', ['Yes', 'No'])->nullable();
            $table->integer('vents_count')->nullable();
            $table->enum('fold_cuff_required', ['Yes', 'No', 'Customized'])->nullable();
            $table->decimal('fold_cuff_size', 5)->nullable();
            $table->enum('pleats_required', ['No', '1', '1(Centre Crease)', '2'])->nullable();
            $table->integer('pleats_count')->nullable();
            $table->enum('back_pocket_required', ['No', '1', '2'])->nullable();
            $table->integer('back_pocket_count')->nullable();
            $table->enum('adjustable_belt', ['Yes', 'No'])->nullable();
            $table->enum('suspender_button', ['Yes', 'No'])->nullable();
            $table->string('trouser_position', 50)->nullable();
            $table->string('sleeves', 50)->nullable();
            $table->string('collar', 50)->nullable();
            $table->string('collar_style', 100)->nullable();
            $table->string('pocket', 50)->nullable();
            $table->string('cuffs', 50)->nullable();
            $table->string('client_name_required', 50)->nullable();
            $table->string('client_name_place', 50)->nullable();
            $table->enum('client_name_options', ['Cuffs', 'Pocket', 'Pocket Space', 'Col'])->nullable();
            $table->string('cuff_style', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['Process', 'Hold'])->default('Hold');
            $table->enum('tl_status', ['Pending', 'Approved', 'Hold'])->default('Pending');
            $table->enum('admin_status', ['Pending', 'Approved', 'Hold'])->default('Pending');
            $table->enum('assigned_team', ['sales', 'production'])->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
