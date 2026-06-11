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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bill_id')->nullable();
            $table->string('business_type')->nullable()->default('TEXTILES');
            $table->unsignedBigInteger('customer_id')->nullable()->index('customer_id_fk');
            $table->unsignedBigInteger('created_by')->nullable()->index('created_by_fk');
            $table->unsignedBigInteger('team_lead_id')->nullable()->index('team_lead_fk');
            $table->string('order_number')->unique();
            $table->string('invoice_type')->nullable()->default('invoice');
            $table->string('prefix')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_image')->nullable()->comment('	aka Client Image');
            $table->string('billing_address')->nullable();
            $table->string('billing_landmark')->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_state', 100)->nullable();
            $table->string('billing_country', 100)->nullable();
            $table->string('billing_pin', 20)->nullable();
            $table->string('shipping_address')->nullable();
            $table->decimal('total_product_amount', 15)->default(0);
            $table->decimal('air_mail', 15)->default(0);
            $table->decimal('total_amount', 10)->nullable();
            $table->text('physical_order_bill_book')->nullable();
            $table->string('verified_video')->nullable();
            $table->timestamps();
            $table->enum('status', ['Approval Pending from TL', 'Partial Approved By Admin', 'Received at Production', 'Ready for Delivery', 'Partial Delivered By Production', 'Fully Delivered By Production', 'Cancelled', 'Returned', 'Received by Sales Team', 'Delivered to Customer', 'Partial Delivered to Customer', 'Partial Approved By TL', 'Received at Sales', 'Fully Approved By TL', 'Fully Approved By Admin', 'On Hold'])->default('Approval Pending from TL');
            $table->string('skip_order_reason')->nullable();
            $table->string('source')->nullable();
            $table->string('reference')->nullable();
            $table->decimal('ht_amount', 15)->default(0);
            $table->decimal('tva_amount', 15)->default(0);
            $table->decimal('ca_amount', 15)->default(0);
            $table->timestamp('last_payment_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('invoice_date')->nullable();
            $table->decimal('paid_amount', 15)->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
