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
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->foreign(['invoice_id'], 'invoice_payments_ibfk_1')->references(['id'])->on('invoices')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['payment_collection_id'], 'invoice_payments_ibfk_2')->references(['id'])->on('payment_collections')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropForeign('invoice_payments_ibfk_1');
            $table->dropForeign('invoice_payments_ibfk_2');
        });
    }
};
