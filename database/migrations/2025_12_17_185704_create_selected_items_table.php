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
        Schema::create('selected_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('temp_user_id')->nullable()->constrained('temp_users')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('selected_items')->nullable();
            $table->integer('total_quantity')->nullable();
            $table->integer('total_amount')->nullable();
            $table->string('payment_method')->nullable();
            $table->tinyInteger('payment_status')->default(0)->comment('0: not paid, 1: pending, 2: paid');
            $table->timestamp('payment_date')->nullable();
            $table->text('transaction_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('selected_items');
    }
};