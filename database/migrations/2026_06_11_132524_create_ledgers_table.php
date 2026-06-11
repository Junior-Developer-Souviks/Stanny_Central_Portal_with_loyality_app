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
        Schema::create('ledgers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('user_type', ['staff', 'customer', 'partner', 'supplier'])->default('staff');
            $table->unsignedBigInteger('staff_id')->nullable()->index('ledgers_staff_id_foreign');
            $table->unsignedBigInteger('customer_id')->nullable()->index('ledgers_customer_id_foreign');
            $table->unsignedBigInteger('supplier_id')->nullable()->index('ledgers_supplier_id_foreign');
            $table->unsignedBigInteger('admin_id')->nullable()->index('ledgers_admin_id_foreign');
            $table->unsignedBigInteger('payment_id')->nullable()->index('ledgers_payment_id_foreign');
            $table->unsignedBigInteger('staff_commision_id')->nullable()->index('ledgers_ibfk_5');
            $table->unsignedBigInteger('collection_staff_commission_id')->nullable();
            $table->unsignedBigInteger('store_bad_debt_id')->nullable();
            $table->string('transaction_id', 244)->nullable()->comment('invoice_no / voucher_no');
            $table->double('transaction_amount')->nullable();
            $table->boolean('is_credit')->default(false);
            $table->boolean('is_debit')->default(false);
            $table->enum('bank_cash', ['bank', 'cash', 'wallet'])->default('bank');
            $table->date('entry_date')->nullable();
            $table->string('purpose')->nullable();
            $table->text('purpose_description')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->integer('whatsapp_status')->default(0)->comment('0:Pending, 1:Sent, 2: Cancel');
            $table->dateTime('last_whatsapp')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
