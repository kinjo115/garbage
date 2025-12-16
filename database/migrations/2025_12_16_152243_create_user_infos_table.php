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
        Schema::create('user_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('temp_user_id')->nullable()->constrained('temp_users')->onDelete('set null');
            $table->string('last_name'); // 姓
            $table->string('first_name'); // 名
            $table->foreignId('housing_type_id')->nullable()->constrained('housing_types')->onDelete('set null'); // 住居タイプ
            $table->string('postal_code'); // 郵便番号
            $table->foreignId('prefecture_id')->nullable()->constrained('prefectures')->onDelete('set null'); // 都道府県
            $table->string('city'); // 市区町村
            $table->string('town')->nullable(); // 町名
            $table->string('chome')->nullable(); // 丁目
            $table->string('building_number')->nullable(); // 番
            $table->string('house_number')->nullable(); // 号
            $table->string('building_name')->nullable(); // 建物名（事業所名など）
            $table->string('apartment_name')->nullable(); // マンション名
            $table->string('apartment_number')->nullable(); // 部屋番号
            $table->string('phone_number')->nullable(); // 電話番号
            $table->string('emergency_contact')->nullable(); // 緊急連絡先
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_infos');
    }
};