<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            // 買家
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // 商品 (來自代購貼文的商品)
            $table->foreignId('post_product_id')->constrained('post_products')->onDelete('cascade');

            // 數量
            $table->integer('quantity')->default(1);

            $table->timestamps();

            // 同一個用戶對同一個商品只會有一筆購物車紀錄 (重複加入應該是更新數量)
            $table->unique(['user_id', 'post_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};