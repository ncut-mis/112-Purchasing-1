<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 檢查表是否存在，如果不存在則建立
        if (!Schema::hasTable('agent_notifications')) {
            Schema::create('agent_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agent_id');
                $table->unsignedBigInteger('buyer_id');
                $table->unsignedBigInteger('request_list_id');
                $table->string('title');
                $table->text('content');
                $table->boolean('is_read')->default(false);
                $table->boolean('is_selected')->default(false); // 順便建立
                $table->timestamps();

                $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('request_list_id')->references('id')->on('request_lists')->onDelete('cascade');
            });
        } else {
            // 如果表已經存在，則只新增欄位
            Schema::table('agent_notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('agent_notifications', 'is_selected')) {
                    $table->boolean('is_selected')->default(false)->after('is_read');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 根據需求決定是要刪除欄位還是刪除整張表
        // 如果表是這支檔案建立的，用這個：
        Schema::dropIfExists('agent_notifications');
        
        // 如果只是想在回滾時移除欄位，用這個：
        // Schema::table('agent_notifications', function (Blueprint $table) {
        //     $table->dropColumn('is_selected');
        // });
    }
};