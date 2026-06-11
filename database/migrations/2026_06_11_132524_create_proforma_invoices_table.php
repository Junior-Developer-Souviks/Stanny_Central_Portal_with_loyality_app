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
        Schema::create('proforma_invoices', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('customer_id')->nullable()->index('customer_id_fk_from_proforma_customers');
            $table->string('proforma_number')->nullable()->unique('proforma_number');
            $table->date('date')->nullable();
            $table->decimal('subtotal', 15)->nullable();
            $table->decimal('total_amount', 15)->nullable();
            $table->text('conditions')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('delivery_period')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_invoices');
    }
};
