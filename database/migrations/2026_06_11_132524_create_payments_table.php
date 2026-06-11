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
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable()->index('payments_order_id_foreign');
            $table->decimal('paid_amount', 10)->nullable();
            $table->unsignedBigInteger('admin_id')->nullable()->index('payments_admin_id_foreign');
            $table->unsignedBigInteger('stuff_id')->nullable()->index('payments_stuff_id_foreign');
            $table->unsignedBigInteger('supplier_id')->nullable()->index('payments_supplier_id_foreign');
            $table->unsignedBigInteger('customer_id')->nullable()->index('payments_customer_id_foreign');
            $table->unsignedBigInteger('expense_id')->nullable()->index('payments_expense_id_foreign');
            $table->unsignedBigInteger('service_slip_id')->nullable();
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->string('payment_for')->nullable();
            $table->string('payment_in')->nullable();
            $table->enum('bank_cash', ['Bank', 'Cash'])->nullable();
            $table->string('voucher_no')->nullable();
            $table->string('image')->nullable();
            $table->date('payment_date')->nullable();
            $table->enum('payment_mode', ['Cash', 'Cheque', 'UPI', 'Bank Transfer', 'neft', 'digital_payment'])->nullable();
            $table->decimal('amount', 10)->nullable();
            $table->string('chq_utr_no')->nullable();
            $table->string('bank_name')->nullable();
            $table->text('narration')->nullable();
            $table->string('expense_proof')->nullable();
            $table->enum('created_from', ['web', 'app'])->nullable();
            $table->boolean('is_gst')->default(false);
            $table->boolean('is_ledger_added')->default(false)->comment('1:Ledger added,0:Not Ledger Added');
            $table->boolean('is_approved')->default(false)->comment('1:Approved,0:not Approved');
            $table->unsignedBigInteger('approved_by')->nullable()->index('payments_ibfk_3');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index('payments_ibfk_4');
            $table->unsignedBigInteger('updated_by')->nullable()->index('payments_ibfk_5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
