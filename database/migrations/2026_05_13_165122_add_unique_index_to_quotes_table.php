<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // 這行才是關鍵：把 request_list_id 和 user_id 綁在一起變成唯一
            $table->unique(['request_list_id', 'user_id'], 'unique_user_quote');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // 復原時移除索引
            $table->dropUnique('unique_user_quote');
        });
    }
};