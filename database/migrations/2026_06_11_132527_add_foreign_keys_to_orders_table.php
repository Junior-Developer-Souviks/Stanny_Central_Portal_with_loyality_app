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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign(['created_by'], 'created_by_fk')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['customer_id'], 'customer_id_fk')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['team_lead_id'], 'team_lead_fk')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('created_by_fk');
            $table->dropForeign('customer_id_fk');
            $table->dropForeign('team_lead_fk');
        });
    }
};
