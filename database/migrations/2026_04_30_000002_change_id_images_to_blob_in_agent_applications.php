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
        if (!Schema::hasTable('agent_applications')) {
            return;
        }

        Schema::table('agent_applications', function (Blueprint $table) {
            // 將 id_image_front 和 id_image_back 從 string 改為 mediumblob
            DB::statement('ALTER TABLE `agent_applications` MODIFY `id_image_front` MEDIUMBLOB NULL');
            DB::statement('ALTER TABLE `agent_applications` MODIFY `id_image_back` MEDIUMBLOB NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('agent_applications')) {
            return;
        }

        Schema::table('agent_applications', function (Blueprint $table) {
            // 如果需要回滾，改回 string 類型
            $table->string('id_image_front')->nullable()->change();
            $table->string('id_image_back')->nullable()->change();
        });
    }
};
