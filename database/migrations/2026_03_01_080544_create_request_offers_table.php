<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_offers', function (Blueprint $table) {
            $table->id();

            // 哪一張請購單
            $table->foreignId('request_list_id')->constrained()->onDelete('cascade');

            // 哪一位代購人 (User ID)
            $table->foreignId('agent_user_id')->constrained('users')->onDelete('cascade');

            // 代購人報價 (總金額：含商品費+代購費+運費)
            $table->decimal('offered_price', 10, 2);
            $table->string('currency')->default('TWD');

            // 預計交貨日期
            $table->date('delivery_date');

            // 給買家的留言 (例如：我可以幫你買，但我下週才回國，可以嗎？)
            $table->text('message')->nullable();

            // 狀態：
            // pending: 等待買家審核
            // accepted: 買家接受了 (此時請購單狀態會變 matched)
            // rejected: 買家拒絕了
            // withdrawn: 代購人自己撤回報價
            $table->enum('status', ['pending', 'accepted', 'rejected', 'withdrawn'])->default('pending');

            $table->timestamps();
            
            // 為了避免同一個代購人重複對同一張單報價多次 (可選限制)
            // $table->unique(['request_list_id', 'agent_user_id']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_offers');
    }
};