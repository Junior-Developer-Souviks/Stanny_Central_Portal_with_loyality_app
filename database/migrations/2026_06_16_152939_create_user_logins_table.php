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
        Schema::create('user_logins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('country_code', 10)->nullable();
            $table->string('mobile', 20)->nullable()->unique();
            $table->string('email', 191)->nullable()->unique();
            $table->boolean('is_verified')->default(false);
            $table->string('otp', 4)->nullable();
            $table->string('mpin')->nullable();
            $table->string('device_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logins');
    }
};
