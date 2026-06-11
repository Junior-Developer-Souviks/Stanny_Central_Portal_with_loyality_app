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
        Schema::table('city_user', function (Blueprint $table) {
            $table->foreign(['city_id'], 'city_id_fk')->references(['id'])->on('cities')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['user_id'], 'user_id_fk')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('city_user', function (Blueprint $table) {
            $table->dropForeign('city_id_fk');
            $table->dropForeign('user_id_fk');
        });
    }
};
