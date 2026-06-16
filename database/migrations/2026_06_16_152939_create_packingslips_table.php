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
        Schema::create('packingslips', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable()->index('packingslips_order_id_foreign');
            $table->unsignedBigInteger('customer_id')->nullable()->index('packingslips_customer_id_foreign');
            $table->string('slipno', 100)->nullable();
            $table->boolean('is_disbursed')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->unsignedBigInteger('disbursed_by')->nullable();
            $table->timestamp('disbursed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packingslips');
    }
};
