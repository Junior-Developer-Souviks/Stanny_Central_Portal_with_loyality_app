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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreign(['user_id'], 'fk_wallet_user')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['redeemed_by'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign('fk_wallet_user');
            $table->dropForeign('wallet_transactions_redeemed_by_foreign');
        });
    }
};
