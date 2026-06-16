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
        Schema::create('todo_lists', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('customer_id')->nullable();
            $table->integer('user_id');
            $table->integer('created_by');
            $table->enum('todo_type', ['Order', 'Payment', 'Delivery', 'Delivery Return', 'Cheque Deposit']);
            $table->date('todo_date')->nullable();
            $table->text('remark')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todo_lists');
    }
};
