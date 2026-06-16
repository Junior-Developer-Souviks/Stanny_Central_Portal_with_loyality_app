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
        Schema::table('order_multiple_files', function (Blueprint $table) {
            $table->foreign(['order_id'], 'order_id_ibfk')->references(['id'])->on('orders')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_multiple_files', function (Blueprint $table) {
            $table->dropForeign('order_id_ibfk');
        });
    }
};
