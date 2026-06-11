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
        Schema::create('salesman_billing_number', function (Blueprint $table) {
            $table->integer('id', true);
            $table->unsignedBigInteger('salesman_id')->index('salesman_id');
            $table->string('start_no');
            $table->string('end_no');
            $table->integer('no_of_used')->nullable();
            $table->integer('total_count')->nullable()->comment('total no of bill count');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesman_billing_number');
    }
};
