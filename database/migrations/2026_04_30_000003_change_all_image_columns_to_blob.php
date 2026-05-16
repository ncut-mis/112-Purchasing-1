<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 修改 agent_posts 表的 cover_image 欄位
        if (Schema::hasTable('agent_posts')) {
            Schema::table('agent_posts', function (Blueprint $table) {
                DB::statement('ALTER TABLE `agent_posts` MODIFY `cover_image` MEDIUMBLOB NULL');
            });
        }

        // 修改 post_products 表的 image_path 欄位
        if (Schema::hasTable('post_products')) {
            Schema::table('post_products', function (Blueprint $table) {
                DB::statement('ALTER TABLE `post_products` MODIFY `image_path` MEDIUMBLOB NULL');
            });
        }

        // 修改 request_items 表的 reference_image 欄位
        if (Schema::hasTable('request_items')) {
            Schema::table('request_items', function (Blueprint $table) {
                DB::statement('ALTER TABLE `request_items` MODIFY `reference_image` MEDIUMBLOB NULL');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 回滾 agent_posts 表的 cover_image 欄位
        if (Schema::hasTable('agent_posts')) {
            Schema::table('agent_posts', function (Blueprint $table) {
                $table->string('cover_image')->nullable()->change();
            });
        }

        // 回滾 post_products 表的 image_path 欄位
        if (Schema::hasTable('post_products')) {
            Schema::table('post_products', function (Blueprint $table) {
                $table->string('image_path')->nullable()->change();
            });
        }

        // 回滾 request_items 表的 reference_image 欄位
        if (Schema::hasTable('request_items')) {
            Schema::table('request_items', function (Blueprint $table) {
                $table->string('reference_image')->nullable()->change();
            });
        }
    }
};
