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
        Schema::create('payment_revokes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id')->index('payment_revokes_customer_id_foreign');
            $table->unsignedBigInteger('done_by')->index('payment_revokes_done_by_foreign');
            $table->string('voucher_no');
            $table->double('collection_amount')->nullable();
            $table->longText('paymentcollection_data_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_revokes');
    }
};
