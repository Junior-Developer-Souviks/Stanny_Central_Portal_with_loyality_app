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
        Schema::create('day_cash_entry', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->enum('type', ['collected', 'given']);
            $table->date('payment_date')->nullable();
            $table->integer('amount')->nullable();
            $table->integer('payment_cash')->nullable();
            $table->integer('payment_digital')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_cash_entry');
    }
};
