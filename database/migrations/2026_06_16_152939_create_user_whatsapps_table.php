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
        Schema::create('user_whatsapps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('supplier_id')->nullable()->index('user_whatsapps_supplier_id_foreign');
            $table->unsignedBigInteger('user_id')->nullable()->index('user_whatsapps_user_id_foreign');
            $table->string('country_code', 10);
            $table->string('whatsapp_number', 20)->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_whatsapps');
    }
};
