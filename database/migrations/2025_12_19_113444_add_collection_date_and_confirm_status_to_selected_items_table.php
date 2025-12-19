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
        Schema::table('selected_items', function (Blueprint $table) {
            $table->date('collection_date')->nullable()->after('transaction_id');
            $table->tinyInteger('confirm_status')->default(0)->comment('0: not confirmed, 1: confirmed')->after('collection_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('selected_items', function (Blueprint $table) {
            $table->dropColumn(['collection_date', 'confirm_status']);
        });
    }
};
