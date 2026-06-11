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
        Schema::table('packingslips', function (Blueprint $table) {
            $table->foreign(['created_by'], 'created_by_f_keys')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['customer_id'], 'customer_id_foreign_keys')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['disbursed_by'], 'disbursed_by_foreign_key')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['updated_by'], 'updated_by_foreign_key')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packingslips', function (Blueprint $table) {
            $table->dropForeign('created_by_f_keys');
            $table->dropForeign('customer_id_foreign_keys');
            $table->dropForeign('disbursed_by_foreign_key');
            $table->dropForeign('updated_by_foreign_key');
        });
    }
};
