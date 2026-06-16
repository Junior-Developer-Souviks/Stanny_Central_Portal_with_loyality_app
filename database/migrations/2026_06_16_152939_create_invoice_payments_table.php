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
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoice_id')->nullable()->index('invoice_payments_invoice_id_foreign');
            $table->unsignedBigInteger('payment_collection_id')->nullable()->index('invoice_payments_payment_collection_id_foreign');
            $table->double('invoice_amount')->comment('invoice\'s net amount');
            $table->double('vouchar_amount');
            $table->double('paid_amount')->comment('payment amount');
            $table->double('rest_amount');
            $table->boolean('is_commisionable')->default(false)->comment('for staff');
            $table->string('invoice_no')->nullable();
            $table->string('voucher_no')->nullable()->comment('payment_receipt');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
