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

            // 為每個買家收集本回合的訂單，以便按比例分配狀態
            $buyerOrders = [];

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

                $buyerOrders[] = [
                    'buyer_id' => $buyer->id,
                    'seller_id' => $post->user_id,
                    'source_type' => AgentPost::class,
                    'source_id' => $post->id,
                    'items_total' => $itemsTotal,
                    'shipping_fee' => $shippingFee,
                    'platform_fee' => $platformFee,
                    'total_amount' => $totalAmount,
                    'currency' => 'TWD',
                    'order_items' => $orderItemsPayload,
                    'selected_products' => $selectedProducts,
                ];
            }

            // 現在為該買家的所有訂單分配狀態
            $this->assignOrderStatuses($buyerOrders, $seedCounter);
        }

        $this->command?->info('已完成 AgentPost 跟單比例模擬（含多樣化訂單狀態分配）。');
    }

    /**
     * 為買家的訂單分配多樣的狀態：
     * - pending_payment (未付款) 1~2 個
     * - wait-for-ship (待出貨) 2~6 個
     * - shipped (已出貨) 2~4 個
     * - arrivaled (已到貨) 1~3 個
     */
    private function assignOrderStatuses(array &$buyerOrders, &$seedCounter): void
    {
        if (empty($buyerOrders)) {
            return;
        }

        $orderCount = count($buyerOrders);

        // 定義狀態分配
        $statusDistribution = [
            'pending_payment' => ['min' => 1, 'max' => 2],
            'wait-for-ship' => ['min' => 2, 'max' => 6],
            'shipped' => ['min' => 2, 'max' => 4],
            'arrivaled' => ['min' => 1, 'max' => 3],
        ];

        // 根據訂單數量進行分配
        $statusCounts = [];
        $remainingOrders = $orderCount;

        foreach ($statusDistribution as $status => $range) {
            $min = $range['min'];
            $max = $range['max'];
            
            if ($remainingOrders <= 0) {
                $statusCounts[$status] = 0;
            } elseif ($remainingOrders <= $min) {
                $statusCounts[$status] = min($remainingOrders, $min);
                $remainingOrders = 0;
            } else {
                // 根據剩餘訂單數量動態決定該狀態分配多少
                $allocate = random_int($min, min($max, $remainingOrders));
                $statusCounts[$status] = $allocate;
                $remainingOrders -= $allocate;
            }
        }

        // 如果還有剩餘訂單，隨機分配到各狀態
        if ($remainingOrders > 0) {
            $statuses = array_keys($statusDistribution);
            for ($i = 0; $i < $remainingOrders; $i++) {
                $randomStatus = $statuses[array_rand($statuses)];
                $statusCounts[$randomStatus]++;
            }
        }

        // 打亂訂單順序然後分配狀態
        shuffle($buyerOrders);
        $orderIndex = 0;
        $statuses = array_keys($statusCounts);

        foreach ($statuses as $status) {
            for ($i = 0; $i < $statusCounts[$status]; $i++) {
                if ($orderIndex >= count($buyerOrders)) {
                    break 2;
                }

                $orderData = $buyerOrders[$orderIndex];
                $this->createOrderWithStatus(
                    $orderData,
                    $status,
                    ++$seedCounter
                );
                $orderIndex++;
            }
        }
    }

    /**
     * 建立單筆訂單並根據狀態設定相應的時間戳
     */
    private function createOrderWithStatus(array $orderData, string $status, int $seedCounter): void
    {
        $now = now();
        $paymentData = [
            'pending_payment' => ['paid_at' => null],
            'wait-for-ship' => ['paid_at' => $now->copy()->subDays(random_int(0, 5))],
            'shipped' => [
                'paid_at' => $now->copy()->subDays(random_int(3, 8)),
                'shipped_at' => $now->copy()->subDays(random_int(0, 3)),
            ],
            'arrivaled' => [
                'paid_at' => $now->copy()->subDays(random_int(5, 14)),
                'shipped_at' => $now->copy()->subDays(random_int(2, 10)),
            ],
        ];

        $payment = $paymentData[$status] ?? [];

        $order = Order::create([
            'order_no' => sprintf('SEED%s%05d', $now->format('YmdHis'), $seedCounter),
            'buyer_id' => $orderData['buyer_id'],
            'seller_id' => $orderData['seller_id'],
            'source_type' => $orderData['source_type'],
            'source_id' => $orderData['source_id'],
            'items_total' => $orderData['items_total'],
            'shipping_fee' => $orderData['shipping_fee'],
            'platform_fee' => $orderData['platform_fee'],
            'total_amount' => $orderData['total_amount'],
            'currency' => $orderData['currency'],
            'status' => $status,
            'payment_method' => 'seed_simulated',
            'shipping_method' => 'seed_simulated',
            'tracking_number' => 'SEED-TRK-' . strtoupper(substr(md5((string) mt_rand()), 0, 8)),
            'recipient_data' => [
                'name' => User::find($orderData['buyer_id'])->name ?? 'Seeder User',
                'phone' => '09' . random_int(10000000, 99999999),
                'address' => 'Seeder 測試地址',
            ],
            'note' => "Seeder 模擬訂單 - 狀態：{$status}",
            'paid_at' => $payment['paid_at'] ?? null,
            'shipped_at' => $payment['shipped_at'] ?? null,
        ]);

        $order->items()->createMany($orderData['order_items']);

        // 更新商品的已銷售數量
        foreach ($orderData['order_items'] as $item) {
            $product = $orderData['selected_products']->firstWhere('id', $item['product_id']);
            if ($product) {
                $product->increment('sold_quantity', (int) $item['quantity']);
            }
        }

    }
}