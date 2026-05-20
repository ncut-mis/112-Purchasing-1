<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 先把舊的 reviews 表刪掉，再重建新版
        Schema::dropIfExists('reviews');

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete(); // 評價人（請託人）
            $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete(); // 被評價人（代購人）
            $table->string('reviewable_type');  // 來源類型：App\Models\Quote 或 App\Models\Order
            $table->unsignedBigInteger('reviewable_id'); // 來源 ID
            $table->tinyInteger('rating');       // 1~5 星
            $table->text('comment')->nullable(); // 文字評論
            $table->timestamps();

            // 一筆來源只能評一次
            $table->unique(['reviewer_id', 'reviewable_type', 'reviewable_id'], 'unique_review');
            $table->index(['reviewable_type', 'reviewable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};