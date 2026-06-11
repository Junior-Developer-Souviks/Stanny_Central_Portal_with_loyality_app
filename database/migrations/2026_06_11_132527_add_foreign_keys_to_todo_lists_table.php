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
        Schema::table('todo_lists', function (Blueprint $table) {
            $table->foreign(['created_by'], 'created_by_foreign_key')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['customer_id'], 'customer_id_foreign_key')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['user_id'], 'user_id_foreign_key')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('todo_lists', function (Blueprint $table) {
            $table->dropForeign('created_by_foreign_key');
            $table->dropForeign('customer_id_foreign_key');
            $table->dropForeign('user_id_foreign_key');
        });
    }
};
