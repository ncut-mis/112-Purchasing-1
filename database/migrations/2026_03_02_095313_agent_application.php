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
        Schema::create('agent_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');              // 姓名
            $table->string('country');           // 國家
            $table->string('phone');             // 電話
            $table->string('id_number');         // 身分證字號
            $table->string('id_image_front');    // 身分證正面圖片路徑
            $table->string('id_image_back');     // 身分證反面圖片路徑
            $table->string('status')->default('pending'); // 狀態：pending, approved, rejected
            $table->text('admin_remark')->nullable();     // 管理員備註
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_applications');
    }
};