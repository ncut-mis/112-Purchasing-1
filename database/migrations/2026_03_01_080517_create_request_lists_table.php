<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_lists', function (Blueprint $table) {
            $table->id();

            // 請購人 (買家)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // 標題 (例如：急徵 11月韓國免稅店代購)
            $table->string('title');

            // 代購國家/地區 (代購人搜尋的關鍵)
            $table->string('country')->index();
            $table->string('city')->nullable();

            // 建立請購清單時輸入的購買店家資訊
            $table->string('store_name')->nullable();
            $table->string('address_detail')->nullable();

            // 希望收到的截止日期 (過期就自動失效)
            $table->date('deadline');

            // 總預算 (選填，有些買家不清楚價格)
            $table->decimal('budget_total', 10, 2)->nullable();
            $table->string('currency')->default('TWD');

            // 狀態：
            // pending: 等待接單
            // offered: 有人報價了
            // matched: 已確認代購人 (進入訂單流程)
            // completed: 已完成
            // cancelled: 已取消
            $table->enum('status', ['pending', 'offered', 'matched', 'completed', 'cancelled'])->default('pending');

            // 備註說明
            $table->text('note')->nullable();

            // 保留建立請購清單時使用者輸入/選擇的完整欄位快照
            $table->json('form_snapshot')->nullable();

            //人跟時間
            
            $table->text('people')->nullable();
            $table->text('time')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_lists');
    }
};
