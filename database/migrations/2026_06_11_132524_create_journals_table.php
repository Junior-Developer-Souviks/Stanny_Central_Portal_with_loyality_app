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
        Schema::create('journals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payment_id')->nullable()->index('journals_payment_id_foreign');
            $table->double('transaction_amount')->nullable();
            $table->boolean('is_credit')->default(false);
            $table->boolean('is_debit')->default(false);
            $table->string('bank_cash')->default('bank');
            $table->string('purpose')->nullable();
            $table->text('purpose_description')->nullable();
            $table->string('purpose_id')->nullable()->comment('invoice_no / voucher_no');
            $table->date('entry_date')->nullable();
            $table->boolean('is_gst')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
