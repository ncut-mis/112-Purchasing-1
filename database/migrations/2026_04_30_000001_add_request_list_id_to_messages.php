<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // 如果還沒有這個欄位才加
            if (!Schema::hasColumn('messages', 'request_list_id')) {
                $table->foreignId('request_list_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('request_lists')
                      ->onDelete('cascade');
            }
        });
    }
 
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['request_list_id']);
            $table->dropColumn('request_list_id');
        });
    }
};
 