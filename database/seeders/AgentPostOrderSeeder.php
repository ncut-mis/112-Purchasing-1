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
            $this->command?->warn('略過 AgentPostOrderSeeder：缺少使用者或代購團資料。');
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

 $postProducts = $post->products()->where('is_active', true)->get();
                if ($postProducts->isEmpty()) {
                    continue;
                }

                $availableProducts = $postProducts
                    ->filter(function ($product) {
                        if (is_null($product->max_quantity)) {
                            return true;
                        }

                        return (int) $product->sold_quantity < (int) $product->max_quantity;
                    })
                    ->values();

                if ($availableProducts->isEmpty()) {
                    continue;
                }

                $pickCount = min($availableProducts->count(), random_int(1, 2));
                $selectedProducts = $availableProducts->shuffle()->take($pickCount)->values();
                $orderItemsPayload = [];
                $itemsTotal = 0;

                foreach ($selectedProducts as $product) {
                    $remaining = is_null($product->max_quantity)
                        ? 3
                        : max(0, (int) $product->max_quantity - (int) $product->sold_quantity);

                    if ($remaining < 1) {
                        continue;
                    }

                    $quantity = random_int(1, min(3, $remaining));
                    $price = (float) $product->price;
                    $subtotal = $price * $quantity;
                    $itemsTotal += $subtotal;

                    $orderItemsPayload[] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'options' => null,
                        'price' => $price,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal,
                    ];
                }

                if ($itemsTotal <= 0) {
                    continue;
                }

                $shippingFee = random_int(0, 200);
                $platformFee = (int) round($itemsTotal * 0.03);
                $totalAmount = $itemsTotal + $shippingFee + $platformFee;

                $order = Order::create([
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

                $order->items()->createMany($orderItemsPayload);

                foreach ($orderItemsPayload as $item) {
                    $product = $selectedProducts->firstWhere('id', $item['product_id']);
                    if (! $product) {
                        continue;
                    }

                    $product->increment('sold_quantity', (int) $item['quantity']);
                }
            }
        }

        $this->command?->info('已完成 AgentPost 跟單比例模擬（含熱門貼文高跟單權重）。');
    }
}