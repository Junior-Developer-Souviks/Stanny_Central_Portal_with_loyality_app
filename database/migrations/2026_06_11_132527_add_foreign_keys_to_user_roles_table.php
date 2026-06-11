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
        Schema::table('user_roles', function (Blueprint $table) {
            $table->foreign(['designation_id'], 'designation_foreign_key')->references(['id'])->on('designation')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['role_id'], 'role_id_fk')->references(['id'])->on('roles')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropForeign('designation_foreign_key');
            $table->dropForeign('role_id_fk');
        });
    }
};
