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
        Schema::table('user_infos', function (Blueprint $table) {
            $table->decimal('home_latitude', 10, 8)->nullable()->comment('自宅位置の緯度');
            $table->decimal('home_longitude', 11, 8)->nullable()->comment('自宅位置の経度');
            $table->decimal('disposal_latitude', 10, 8)->nullable()->comment('排出位置の緯度');
            $table->decimal('disposal_longitude', 11, 8)->nullable()->comment('排出位置の経度');
            $table->boolean('apply_after_building')->default(false)->comment('建物が建ってから初めて申し込む');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_infos', function (Blueprint $table) {
            $table->dropColumn([
                'home_latitude',
                'home_longitude',
                'disposal_latitude',
                'disposal_longitude',
                'apply_after_building',
            ]);
        });
    }
};
