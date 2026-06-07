<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\RequestList;
use App\Models\Review;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    // 評價標題與內容的資料庫
    private array $reviewTexts = [
        // 5星評價
        5 => [
            '非常滿意！代購人非常用心，商品品質絕佳。',
            '超級讚！代購人服務態度很好，效率高，商品完好無損。',
            '完美體驗！代購人的選品眼光一流，交貨也很準時。',
            '五星必給！代購人親切專業，購買流程非常順暢。',
            '推薦給所有人！代購人值得信任，品質有保障。',
        ],
        // 4星評價
        4 => [
            '很滿意，但包裝可以更精心一點。',
            '商品品質不錯，就是配送時間稍微有點長。',
            '代購人很用心，只是通訊有時候回應慢一點。',
            '總體來說很好，期待下次合作。',
            '品質佳、服務親切，只是價格稍微偏高。',
        ],
        // 3星評價
        3 => [
            '還可以接受，但有些細節可以改進。',
            '商品如期送達，品質還算不錯。',
            '一般般，中規中矩的代購體驗。',
            '基本滿足需求，但沒有特別出色。',
            '合作沒問題，但感覺效率可以再提升。',
        ],
        // 2星評價
        2 => [
            '有些失望，商品配送有延遲。',
            '品質不如預期，代購人的溝通有待改進。',
            '整體體驗一般，建議改進服務品質。',
            '商品到達時有點受損，希望下次能更小心。',
            '體驗不太理想，但商品還是能用。',
        ],
    ];

    public function run(): void
    {
        $this->command->info('開始建立評價資料...');

        $reviewCount = 0;

        // ========================================
        // Part 1: 為 Quote 類型的訂單創建評價
        // ========================================
        $this->command->info('處理請託單報價的評價...');
        
        // Step 1: 處理已有報價的請託單
        $existingWithQuotes = RequestList::whereHas('quotes')
            ->with(['quotes', 'user'])
            ->get();

        $this->command->info('找到 ' . $existingWithQuotes->count() . ' 筆有報價的請託單');

        // Step 2: 為已存在的報價創建評價
        foreach ($existingWithQuotes as $requestList) {
            // 跳過 ID=1 的用戶
            if ($requestList->user_id === 1) {
                continue;
            }

            $acceptedQuote = $requestList->quotes()
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'rejected')
                ->where('status', '!=', 'returned')
                ->first();

            if (!$acceptedQuote) {
                continue;
            }

            // 檢查是否已經有評價了
            $existingReview = Review::where([
                'reviewer_id' => $requestList->user_id,
                'reviewable_type' => 'App\\Models\\Quote',
                'reviewable_id' => $acceptedQuote->id,
            ])->exists();

            if ($existingReview) {
                continue;
            }

            $rating = $this->generateWeightedRating();
            $comments = $this->reviewTexts[$rating];
            $comment = $comments[array_rand($comments)];

            Review::create([
                'reviewer_id' => $requestList->user_id,
                'reviewee_id' => $acceptedQuote->user_id,
                'reviewable_type' => 'App\\Models\\Quote',
                'reviewable_id' => $acceptedQuote->id,
                'rating' => $rating,
                'comment' => $comment,
                'created_at' => $acceptedQuote->updated_at->addDays(rand(1, 5)),
                'updated_at' => $acceptedQuote->updated_at->addDays(rand(1, 5)),
            ]);

            $reviewCount++;
        }

        // Step 3: 為一些請託單創建模擬的完成狀態和報價
        $randomRequests = RequestList::where('id', '>', 1)  // 排除 ID=1 的用戶
            ->whereDoesntHave('quotes')  // 尋找沒有報價的請託單
            ->where('status', 'pending')
            ->limit(30)  // 最多處理30個
            ->get();

        $agents = User::where('role', 'seller')
            ->orWhereHas('agentApplication', function ($query) {
                $query->where('status', 'approved');
            })
            ->where('id', '!=', 1)
            ->get();

        if (!$agents->isEmpty()) {
            foreach ($randomRequests as $requestList) {
                // 隨機選擇一個代購人
                $agent = $agents->random();

                // 創建報價（直接設置為accepted狀態）
                $quoteTotal = $requestList->items->sum(function ($item) {
                    return ($item->expected_price ?? 500) * ($item->quantity ?? 1);
                });

                $quote = Quote::create([
                    'request_list_id' => $requestList->id,
                    'user_id' => $agent->id,
                    'price' => $quoteTotal * (rand(90, 110) / 100),  // 可能稍微加價或折扣
                    'status' => 'arrivaled',  // 直接設置為已到貨
                    'estimated_date' => Carbon::now()->subDays(rand(1, 5)),
                    'comment' => '快速到貨',
                    'created_at' => Carbon::now()->subDays(rand(7, 14)),
                    'updated_at' => Carbon::now()->subDays(rand(1, 3)),
                ]);

                // 為該報價創建評價
                $rating = $this->generateWeightedRating();
                $comments = $this->reviewTexts[$rating];
                $comment = $comments[array_rand($comments)];

                Review::create([
                    'reviewer_id' => $requestList->user_id,
                    'reviewee_id' => $agent->id,
                    'reviewable_type' => 'App\\Models\\Quote',
                    'reviewable_id' => $quote->id,
                    'rating' => $rating,
                    'comment' => $comment,
                    'created_at' => $quote->updated_at->addDays(rand(1, 5)),
                    'updated_at' => $quote->updated_at->addDays(rand(1, 5)),
                ]);

                $reviewCount++;

                // 更新請託單狀態
                $requestList->update([
                    'status' => 'arrivaled',
                    'people' => $agent->id,
                    'updated_at' => Carbon::now()->subDays(rand(1, 3)),
                ]);
            }
        }

        // ========================================
        // Part 2: 為 Order 類型的訂單創建評價（跟團）
        // ========================================
        $this->command->info('處理跟團訂單的評價...');
        
        $completedOrders = Order::where('status', 'completed')
            ->where('buyer_id', '!=', 1)
            ->with(['buyer', 'seller'])
            ->get();

        $this->command->info('找到 ' . $completedOrders->count() . ' 筆已完成的跟團訂單');

        foreach ($completedOrders as $order) {
            // 檢查是否已經有評價了
            $existingReview = Review::where([
                'reviewer_id' => $order->buyer_id,
                'reviewable_type' => 'App\\Models\\Order',
                'reviewable_id' => $order->id,
            ])->exists();

            if ($existingReview) {
                continue;
            }

            $rating = $this->generateWeightedRating();
            $comments = $this->reviewTexts[$rating];
            $comment = $comments[array_rand($comments)];

            Review::create([
                'reviewer_id' => $order->buyer_id,
                'reviewee_id' => $order->seller_id,
                'reviewable_type' => 'App\\Models\\Order',
                'reviewable_id' => $order->id,
                'rating' => $rating,
                'comment' => $comment,
                'created_at' => $order->updated_at->addDays(rand(1, 5)),
                'updated_at' => $order->updated_at->addDays(rand(1, 5)),
            ]);

            $reviewCount++;
        }

        // 為一些pending的Order更新為completed並添加評價
        $pendingOrders = Order::where('status', '!=', 'completed')
            ->where('buyer_id', '!=', 1)
            ->limit(20)
            ->get();

        foreach ($pendingOrders as $order) {
            // 檢查是否已經有評價了
            $existingReview = Review::where([
                'reviewer_id' => $order->buyer_id,
                'reviewable_type' => 'App\\Models\\Order',
                'reviewable_id' => $order->id,
            ])->exists();

            if ($existingReview) {
                continue;
            }

            // 更新訂單狀態為completed
            $order->update([
                'status' => 'completed',
                'updated_at' => Carbon::now()->subDays(rand(1, 5)),
            ]);

            $rating = $this->generateWeightedRating();
            $comments = $this->reviewTexts[$rating];
            $comment = $comments[array_rand($comments)];

            Review::create([
                'reviewer_id' => $order->buyer_id,
                'reviewee_id' => $order->seller_id,
                'reviewable_type' => 'App\\Models\\Order',
                'reviewable_id' => $order->id,
                'rating' => $rating,
                'comment' => $comment,
                'created_at' => $order->updated_at->addDays(rand(1, 5)),
                'updated_at' => $order->updated_at->addDays(rand(1, 5)),
            ]);

            $reviewCount++;
        }

        $this->command->info("成功建立 {$reviewCount} 筆評價！");
    }

    /**
     * 產生加權評分
     * 分佈：50% 5星，30% 4星，15% 3星，5% 2星
     */
    private function generateWeightedRating(): int
    {
        $random = rand(1, 100);

        if ($random <= 50) {
            return 5;
        } elseif ($random <= 80) {
            return 4;
        } elseif ($random <= 95) {
            return 3;
        } else {
            return 2;
        }
    }
}

