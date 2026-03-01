<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // 發送者
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');

            // 接收者
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');

            // 訊息內容
            $table->text('body');

            // 已讀時間 (null 代表未讀，有時間代表已讀)
            $table->timestamp('read_at')->nullable();

            // 關聯物件 (選填)
            // 如果你想知道這則訊息是在聊哪張訂單或哪張請求，可以加這兩個欄位
            // $table->nullableMorphs('related'); 

            $table->timestamps();
            
            // 建立索引以加快聊天記錄查詢速度
            $table->index(['sender_id', 'receiver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};