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
        Schema::table('order_drafts', function (Blueprint $table) {
            $table->foreign(['admin_id'], 'admin_id_fk')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_drafts', function (Blueprint $table) {
            $table->dropForeign('admin_id_fk');
        });
    }
};
