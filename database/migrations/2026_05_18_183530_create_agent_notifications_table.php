<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up()
    {
        Schema::create('agent_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');       // 接收通知的代購人ID (User ID)
            $table->unsignedBigInteger('buyer_id');       // 發送需求的請購人ID (User ID)
            $table->unsignedBigInteger('request_list_id');// 被推薦的請購清單ID
            $table->string('title');                      // 通知標題 (例如：系統推薦請購單)
            $table->text('content');                      // 通知內文 (簡述清單內容)
            $table->boolean('is_read')->default(false);   // 是否已讀
            $table->timestamps();

            // 索引與外鍵（依據你的資料表微調）
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('request_list_id')->references('id')->on('request_lists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_notifications');
    }
};
