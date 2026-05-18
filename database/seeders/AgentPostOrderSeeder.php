<?php

namespace Database\Seeders;

use App\Models\AgentPost;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class AgentPostOrderSeeder extends Seeder
{
    public function run(): void
    {
        $posts = AgentPost::query()->where('status', 'open')->get();
        $users = User::query()->get();

        if ($posts->isEmpty() || $users->isEmpty()) {
            $this->command?->warn('略過 AgentPostOrderSeeder：缺少使用者或代購貼文資料。');
            return;
        }

        $popularPostIds = $posts
            ->shuffle()
            ->take(max(1, (int) ceil($posts->count() * 0.25)))
            ->pluck('id')
            ->all();

        $seedCounter = 0;

        foreach ($users as $buyer) {
            $candidatePosts = $posts->where('user_id', '!=', $buyer->id)->values();
            if ($candidatePosts->isEmpty()) {
                continue;
            }

            foreach ($candidatePosts as $post) {
                $isPopular = in_array($post->id, $popularPostIds, true);
                $probability = $isPopular ? 42 : 10;

                if (random_int(1, 100) > $probability) {
                    continue;
                }

                $itemsTotal = random_int(300, 6500);
                $shippingFee = random_int(0, 200);
                $platformFee = (int) round($itemsTotal * 0.03);
                $totalAmount = $itemsTotal + $shippingFee + $platformFee;

                Order::create([
                    'order_no' => sprintf('SEED%s%05d', now()->format('YmdHis'), ++$seedCounter),
                    'buyer_id' => $buyer->id,
                    'seller_id' => $post->user_id,
                    'source_type' => AgentPost::class,
                    'source_id' => $post->id,
                    'items_total' => $itemsTotal,
                    'shipping_fee' => $shippingFee,
                    'platform_fee' => $platformFee,
                    'total_amount' => $totalAmount,
                    'currency' => 'TWD',
                    // 依目前 migration 的 enum：pending_payment, wait-for-ship, shipped, completed, cancelled, refunded, arrivaled
                    'status' => 'wait-for-ship',
                    'payment_method' => 'seed_simulated',
                    'paid_at' => now()->subDays(random_int(0, 14)),
                    'shipping_method' => 'seed_simulated',
                    'tracking_number' => 'SEED-TRK-' . strtoupper(substr(md5((string) mt_rand()), 0, 8)),
                    'recipient_data' => [
                        'name' => $buyer->name,
                        'phone' => '09' . random_int(10000000, 99999999),
                        'address' => 'Seeder 測試地址',
                    ],
                    'note' => '熱門代購團跟單比例測試資料',
                ]);
            }
        }

        $this->command?->info('已完成 AgentPost 跟單比例模擬（含熱門貼文高跟單權重）。');
    }
}
