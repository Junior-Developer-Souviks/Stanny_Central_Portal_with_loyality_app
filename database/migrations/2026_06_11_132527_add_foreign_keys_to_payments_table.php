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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign(['customer_id'], 'payments_ibfk_1')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['stuff_id'], 'payments_ibfk_2')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['approved_by'], 'payments_ibfk_3')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['created_by'], 'payments_ibfk_4')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['updated_by'], 'payments_ibfk_5')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['order_id'], 'payments_ibfk_6')->references(['id'])->on('orders')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('payments_ibfk_1');
            $table->dropForeign('payments_ibfk_2');
            $table->dropForeign('payments_ibfk_3');
            $table->dropForeign('payments_ibfk_4');
            $table->dropForeign('payments_ibfk_5');
            $table->dropForeign('payments_ibfk_6');
        });
    }
};
