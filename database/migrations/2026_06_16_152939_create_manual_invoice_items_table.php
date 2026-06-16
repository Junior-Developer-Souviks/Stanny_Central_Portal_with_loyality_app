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
        Schema::create('manual_invoice_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('manual_invoice_id')->index('manual_invoice_id_fk');
            $table->unsignedBigInteger('product_id')->index('product_id_fk');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15)->default(0);
            $table->decimal('total', 15)->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_invoice_items');
    }
};
