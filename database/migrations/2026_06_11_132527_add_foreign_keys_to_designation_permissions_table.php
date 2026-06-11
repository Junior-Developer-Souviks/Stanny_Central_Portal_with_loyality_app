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
        Schema::table('designation_permissions', function (Blueprint $table) {
            $table->foreign(['designation_id'])->references(['id'])->on('designation')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['permission_id'])->references(['id'])->on('permissions')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designation_permissions', function (Blueprint $table) {
            $table->dropForeign('designation_permissions_designation_id_foreign');
            $table->dropForeign('designation_permissions_permission_id_foreign');
        });
    }
};
