<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            // 追蹤者 (買家/請購人) 的 ID
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            // 被追蹤者 (代購人) 的 ID
            $table->foreignId('followed_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // 確保同一個人不能重複追蹤同一個代購人
            $table->unique(['follower_id', 'followed_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};