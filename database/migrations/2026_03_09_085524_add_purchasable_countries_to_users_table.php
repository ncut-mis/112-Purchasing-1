<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 執行遷移。
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 這裡就是你要加的那一行，放在 bio 欄位後面
            $table->text('purchasable_countries')->nullable()->after('bio');
        });
    }

    /**
     * 回滾遷移。
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 如果要還原，就把這個欄位刪掉
            $table->dropColumn('purchasable_countries');
        });
    }
};