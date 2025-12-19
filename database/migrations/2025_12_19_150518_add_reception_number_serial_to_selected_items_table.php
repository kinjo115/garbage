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
            $table->string('reception_number_serial', 5)->nullable()->after('confirm_status')->comment('受付番号のシリアル番号（申請IDの下5桁）');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('selected_items', function (Blueprint $table) {
            $table->dropColumn('reception_number_serial');
        });
    }
};
