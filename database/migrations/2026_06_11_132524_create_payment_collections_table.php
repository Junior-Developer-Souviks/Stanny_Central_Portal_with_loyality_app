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
        Schema::create('payment_collections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id')->nullable()->index('payment_collections_customer_id_foreign');
            $table->unsignedBigInteger('user_id')->nullable()->index('payment_collections_user_id_foreign');
            $table->unsignedBigInteger('admin_id')->nullable()->index('payment_collections_admin_id_foreign');
            $table->unsignedBigInteger('payment_id')->nullable()->index('payment_collections_payment_id_foreign');
            $table->double('collection_amount')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('voucher_no')->nullable()->comment('payment receipt voucher no');
            $table->string('payment_type')->default('cheque')->comment('cheque,neft,cash,digital_payment');
            $table->string('bank_name')->nullable();
            $table->string('cheque_number')->nullable();
            $table->boolean('is_ledger_added')->default(false);
            $table->string('image')->nullable();
            $table->integer('is_approve')->comment('1=approved');
            $table->integer('is_settled')->default(0);
            $table->enum('created_from', ['web', 'app'])->default('app');
            $table->text('remarks')->nullable();
            $table->float('withdrawal_charge')->nullable();
            $table->string('transaction_no')->nullable();
            $table->date('credit_date')->nullable();
            $table->string('cheque_photo')->nullable();
            $table->string('receipt_copy_upload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_collections');
    }
};
