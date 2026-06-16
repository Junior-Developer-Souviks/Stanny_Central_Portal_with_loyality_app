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
        Schema::create('user_banks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('user_banks_user_id_foreign');
            $table->string('account_holder_name')->comment('Banking credentials');
            $table->string('bank_name')->nullable()->comment('Banking credentials');
            $table->string('branch_name')->nullable()->comment('Banking credentials');
            $table->string('bank_account_no')->nullable()->comment('Banking credentials');
            $table->string('ifsc')->nullable()->comment('Banking credentials');
            $table->double('monthly_salary')->nullable()->comment('Salary & allowance');
            $table->double('bonus')->nullable()->comment('Salary & allowance');
            $table->double('past_salaries')->nullable()->comment('Salary & allowance');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_banks');
    }
};
