<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_items', function (Blueprint $table) {
            $table->id();

            // 關聯主表
            $table->foreignId('request_list_id')->constrained()->onDelete('cascade');

            // 商品名稱
            $table->string('name');

            // 參考網址 (非常重要，代購人需要知道去哪買/買哪一款)
            $table->string('reference_url')->nullable();

            // 參考圖片
            $table->string('reference_image')->nullable();

            // 數量
            $table->integer('quantity')->default(1);

            // 單品預算/期望價格
            $table->decimal('expected_price', 10, 2)->nullable();

            // 規格描述 (顏色、尺寸等)
            $table->text('specification')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_items');
    }
};