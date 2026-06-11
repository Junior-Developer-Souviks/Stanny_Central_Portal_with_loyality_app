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
        Schema::table('payment_collections', function (Blueprint $table) {
            $table->foreign(['customer_id'], 'payment_collections_ibfk_1')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['user_id'], 'payment_collections_ibfk_2')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['admin_id'], 'payment_collections_ibfk_3')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_collections', function (Blueprint $table) {
            $table->dropForeign('payment_collections_ibfk_1');
            $table->dropForeign('payment_collections_ibfk_2');
            $table->dropForeign('payment_collections_ibfk_3');
        });
    }
};
