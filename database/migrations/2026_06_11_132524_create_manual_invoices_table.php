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
        Schema::create('manual_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_no')->unique('invoice_no');
            $table->string('customer_name');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('source')->nullable();
            $table->string('reference')->nullable();
            $table->decimal('total_amount', 15)->default(0);
            $table->decimal('ht_amount', 15)->default(0);
            $table->decimal('tva_amount', 15)->default(0);
            $table->decimal('ca_amount', 15)->default(0);
            $table->decimal('paid_amount', 15)->default(0);
            $table->decimal('due_amount', 15)->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_invoices');
    }
};
