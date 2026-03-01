<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // 訂單編號 (唯一字串，例如: ORD-20231027-A1B2)
            $table->string('order_no')->unique();

            // 買家 (支付方)
            $table->foreignId('buyer_id')->constrained('users');
            
            // 賣家 (代購人/收款方)
            $table->foreignId('seller_id')->constrained('users');

            // 訂單來源類型 (例如: 'App\Models\AgentPost' 或 'App\Models\RequestList')
            // 這樣可以知道這張單是怎麼來的
            $table->nullableMorphs('source');

            // 金額相關
            $table->decimal('items_total', 10, 2); // 商品總額
            $table->decimal('shipping_fee', 10, 2)->default(0); // 運費
            $table->decimal('platform_fee', 10, 2)->default(0); // 平台抽成 (預留)
            $table->decimal('total_amount', 10, 2); // 應付總金額
            $table->string('currency')->default('TWD');

            // 訂單狀態
            // pending_payment: 待付款
            // paid: 已付款 (待採購)
            // purchasing: 採購中 (代購人已確認收到款項，正在國外買)
            // shipped: 已出貨 (代購人寄出了)
            // completed: 已完成 (買家收貨確認)
            // cancelled: 已取消
            // refunded: 已退款
            $table->enum('status', [
                'pending_payment', 'paid', 'purchasing', 'shipped', 
                'completed', 'cancelled', 'refunded'
            ])->default('pending_payment');

            // 付款方式 (credit_card, bank_transfer, etc.)
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable(); // 付款時間

            // 物流資訊
            $table->string('shipping_method')->nullable(); // 宅配、超商...
            $table->string('tracking_number')->nullable(); // 物流單號
            $table->timestamp('shipped_at')->nullable(); // 出貨時間

            // 收件資訊快照 (JSON 格式儲存：姓名、電話、地址)
            // 這樣即使 User 修改了個人資料，訂單紀錄也不會變
            $table->json('recipient_data');

            // 備註
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};