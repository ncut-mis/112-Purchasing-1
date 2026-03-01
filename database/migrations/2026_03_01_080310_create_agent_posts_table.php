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
        Schema::create('agent_posts', function (Blueprint $table) {
            $table->id();

            // 關聯到使用者 (代購人)，若使用者被刪除，貼文也一起刪除 (cascade)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // 標題 (例如：10月東京連線代購)
            $table->string('title');

            // 代購國家/地區 (用於篩選，例如：日本、韓國)
            $table->string('country')->index(); 
            // 城市 (選填，例如：大阪)
            $table->string('city')->nullable();

            // 詳細說明 (代購規則、行李重量限制、注意事項等)
            $table->text('description')->nullable();

            // 行程開始日期 (代購人出發日)
            $table->date('start_date');

            // 收單截止日期 (代購人結束接單日)
            $table->date('end_date');

            // 預計出貨日期 (讓買家知道什麼時候能拿到)
            $table->date('estimated_shipping_date')->nullable();

            // 狀態管理：draft(草稿), open(接單中), closed(已截單), completed(已結束)
            $table->enum('status', ['draft', 'open', 'closed', 'completed'])->default('open');

            // 封面圖片路徑 (選填，讓貼文在列表更吸引人)
            $table->string('cover_image')->nullable();

            // 記錄建立與更新時間
            $table->timestamps();
            
            // 軟刪除 (Soft Delete)：刪除時不直接從資料庫消失，保留救援機會
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_posts');
    }
};