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
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('selected_item_id')->constrained('selected_items')->onDelete('cascade');
            $table->string('shop_id')->nullable();
            $table->string('access_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('status')->nullable()->comment('CAPTURE, AUTH, etc.');
            $table->string('job_cd')->nullable()->comment('CAPTURE, AUTH, etc.');
            $table->integer('amount')->nullable();
            $table->integer('tax')->nullable();
            $table->string('currency')->nullable()->default('JPN');
            $table->string('forward')->nullable();
            $table->string('method')->nullable();
            $table->integer('pay_times')->nullable();
            $table->string('tran_id')->nullable();
            $table->string('approve')->nullable();
            $table->string('tran_date')->nullable();
            $table->string('err_code')->nullable();
            $table->text('err_info')->nullable();
            $table->string('pay_type')->nullable();
            $table->json('raw_response')->nullable()->comment('Full GMO response for reference');
            $table->timestamps();

            $table->index('selected_item_id');
            $table->index('order_id');
            $table->index('tran_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_histories');
    }
};
