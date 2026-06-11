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
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable()->index('invoices_order_id_foreign');
            $table->unsignedBigInteger('customer_id')->nullable()->index('invoices_customer_id_foreign');
            $table->unsignedBigInteger('user_id')->nullable()->index('invoices_user_id_foreign')->comment('order placed by whom or staff_id');
            $table->unsignedBigInteger('packingslip_id')->nullable()->index('invoices_ibfk_3');
            $table->string('invoice_no')->nullable();
            $table->double('net_price')->comment('total amount');
            $table->double('required_payment_amount')->nullable()->default(0);
            $table->boolean('payment_status')->default(false)->comment('0:pending;1:half_paid;2:full_paid');
            $table->boolean('is_paid')->default(false);
            $table->unsignedBigInteger('created_by')->nullable()->index('invoices_ibfk_1');
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable()->index('invoices_ibfk_4');
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
