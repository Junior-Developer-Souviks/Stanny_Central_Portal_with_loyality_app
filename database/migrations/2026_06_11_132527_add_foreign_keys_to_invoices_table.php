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
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign(['created_by'], 'invoices_ibfk_1')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['user_id'], 'invoices_ibfk_2')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['packingslip_id'], 'invoices_ibfk_3')->references(['id'])->on('packingslips')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['updated_by'], 'invoices_ibfk_4')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['customer_id'], 'invoices_ibfk_5')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign('invoices_ibfk_1');
            $table->dropForeign('invoices_ibfk_2');
            $table->dropForeign('invoices_ibfk_3');
            $table->dropForeign('invoices_ibfk_4');
            $table->dropForeign('invoices_ibfk_5');
        });
    }
};
