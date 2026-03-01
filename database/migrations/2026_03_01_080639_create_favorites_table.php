<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();

            // 誰收藏的
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // 收藏了什麼？ (Polymorphic)
            // 這會自動建立兩個欄位: favoriteable_id, favoriteable_type
            // type 會存 'App\Models\AgentPost' 或 'App\Models\RequestList'
            $table->morphs('favoriteable');

            $table->timestamps();

            // 避免重複收藏 (同一個用戶不能收藏同一個物件兩次)
            $table->unique(['user_id', 'favoriteable_id', 'favoriteable_type'], 'user_favorite_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};