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
        Schema::create('loyalty_rules', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->decimal('min_amount', 10)->nullable();
            $table->decimal('max_amount', 10)->nullable();
            $table->enum('reward_type', ['lounge', 'points', 'both'])->nullable();
            $table->integer('lounge_visits')->nullable()->default(0);
            $table->enum('points_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('points_value', 10)->nullable();
            $table->integer('points_expiry_days')->nullable();
            $table->integer('lounge_expiry_days')->nullable();
            $table->date('effective_date')->nullable();
            $table->boolean('status')->nullable()->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_rules');
    }
};
