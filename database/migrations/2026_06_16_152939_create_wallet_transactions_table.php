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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->unsignedBigInteger('user_id')->nullable()->index('fk_wallet_user');
            $table->enum('type', ['credit', 'debit', 'expiry'])->nullable();
            $table->decimal('points', 10)->nullable();
            $table->decimal('balance_before', 10)->nullable();
            $table->decimal('balance_after', 10)->nullable();
            $table->integer('lounge_before')->nullable();
            $table->integer('lounge_after')->nullable();
            $table->integer('lounge_used')->nullable();
            $table->integer('lounge_visits')->nullable();
            $table->string('source', 50)->nullable();
            $table->string('channel', 50)->nullable();
            $table->unsignedBigInteger('redeemed_by')->nullable()->index('wallet_transactions_redeemed_by_foreign');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
