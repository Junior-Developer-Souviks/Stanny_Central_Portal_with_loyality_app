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
        Schema::table('expences', function (Blueprint $table) {
            $table->foreign(['parent_id'], 'parent_id_fk')->references(['id'])->on('expences')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expences', function (Blueprint $table) {
            $table->dropForeign('parent_id_fk');
        });
    }
};
