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
        Schema::create('changelog', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('done_by')->index('changelog_ibfk_1');
            $table->integer('order_id')->nullable();
            $table->enum('purpose', ['staff_add', 'staff_update', 'stock_entry_update', 'delivery_proceed', 'extra_stock_entry', 'order_edit'])->nullable();
            $table->longText('data_details')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('changelog');
    }
};
