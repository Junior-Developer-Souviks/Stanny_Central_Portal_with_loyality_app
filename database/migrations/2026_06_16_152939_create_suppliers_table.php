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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('prefix')->nullable();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('country_code_mobile')->nullable();
            $table->string('mobile')->nullable();
            $table->string('country_code_whatsapp')->nullable();
            $table->string('whatsapp_no')->nullable();
            $table->string('country_code_alt_1')->nullable();
            $table->string('alternative_phone_number_1')->nullable();
            $table->string('country_code_alt_2')->nullable();
            $table->string('alternative_phone_number_2')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('billing_landmark')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_pin')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('gst_file')->nullable();
            $table->decimal('credit_limit', 10)->nullable();
            $table->integer('credit_days')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
