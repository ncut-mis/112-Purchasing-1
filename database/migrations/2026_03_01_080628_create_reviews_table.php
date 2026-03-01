<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // 關聯訂單 (評價必定綁定一筆交易)
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            // 評價人 (通常是買家)
            $table->foreignId('reviewer_id')->constrained('users');

            // 被評價人 (通常是代購人)
            $table->foreignId('reviewee_id')->constrained('users');

            // 評分 (1-5顆星)
            $table->unsignedTinyInteger('rating');

            // 評語
            $table->text('comment')->nullable();

            $table->timestamps();

            // 限制：一張訂單只能被同一個人評價一次
            $table->unique(['order_id', 'reviewer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};