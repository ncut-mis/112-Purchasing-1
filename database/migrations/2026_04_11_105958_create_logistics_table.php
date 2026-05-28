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
        Schema::create('logistics', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // 物流名稱
            $table->boolean('status')->default(true); // 啟用狀態
            $table->string('ship_type')->nullable();  // 出貨方式
            $table->string('payment_method')->nullable(); // 付款方式
            $table->json('available_times')->nullable(); // 可配送時段
            $table->string('temp_layer')->nullable(); // 運送溫層
            $table->timestamps();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logistics');
        Schema::table('logistics', function (Blueprint $table) {
        $table->dropForeign(['user_id']); // 先刪除外鍵限制
        $table->dropColumn('user_id');    // 再刪除欄位
    });
    }
};
