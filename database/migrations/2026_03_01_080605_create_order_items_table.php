<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            // 關聯主訂單
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            // 原始商品 ID (選填)
            // 如果是來自 AgentPost，這裡可以記錄 post_product_id，方便統計
            // 如果是來自 RequestList，這裡可能是 null
            $table->unsignedBigInteger('product_id')->nullable();

            // 商品名稱 (快照)
            $table->string('name');

            // 規格 (顏色/尺寸，快照)
            $table->string('options')->nullable();

            // 單價 (快照)
            $table->decimal('price', 10, 2);

            // 數量
            $table->integer('quantity');

            // 小計 (price * quantity)
            $table->decimal('subtotal', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};