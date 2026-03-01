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
        Schema::create('post_products', function (Blueprint $table) {
            $table->id();

            // 關聯到代購貼文 (AgentPost)
            // 如果貼文被刪除，底下的商品也會一併刪除 (cascade)
            $table->foreignId('agent_post_id')->constrained('agent_posts')->onDelete('cascade');

            // 商品名稱
            $table->string('name');

            // 商品描述 (規格、顏色、尺寸、口味等)
            $table->text('description')->nullable();

            // 價格 (使用 decimal 確保金額精確，10位數，小數點後2位)
            $table->decimal('price', 10, 2);

            // 幣別 (預設台幣 TWD，保留擴充性)
            $table->string('currency')->default('TWD');

            // 最大代購數量 (您提到的重點需求)
            // 如果是 null 代表不限量 (只要行李塞得下)
            $table->unsignedInteger('max_quantity')->nullable();

            // 已售出數量 (用來計算是否額滿)
            // 每次有人下單成功，這裡要 +1
            $table->unsignedInteger('sold_quantity')->default(0);

            // 商品圖片路徑 (建議存放相對路徑，如: products/xxx.jpg)
            $table->string('image_path')->nullable();

            // 商品連結 (如果代購人想附上官方網址供參考)
            $table->string('reference_url')->nullable();
            
            // 是否上架中 (代購人可能想暫時隱藏某商品但不想刪除)
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_products');
    }
};