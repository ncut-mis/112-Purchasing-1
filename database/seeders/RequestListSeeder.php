<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Logistics;
use App\Models\RequestList;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RequestListSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('開始建立特定測試情境資料 (郭庭瑋)...');
        $testBuyer = $this->generateFixedScenarios();

        $this->command->info('開始建立隨機請託單資料 (其他使用者)...');
        $this->generateRandomRequests($testBuyer ? $testBuyer->id : null);
    }

    /**
     * ==========================================
     * 1. 建立特定測試情境 (固定狀態與報價)
     * ==========================================
     */
    private function generateFixedScenarios(): ?User
    {
        $buyer = User::updateOrCreate(
            ['email' => 'helper@example.com'],
            [
                'name' => '郭庭瑋',
                'password' => Hash::make('12345678'),
                'role' => 'buyer',
            ]
        );

        $agents = User::whereHas('agentApplication', function ($query) {
                $query->where('status', 'approved');
            })
            ->where('id', '!=', $buyer->id)
            ->take(4)
            ->get();

        if ($agents->count() < 4) {
            $agents = User::where('id', '!=', $buyer->id)
                ->where('role', 'seller')
                ->take(4)
                ->get();
        }

        if ($agents->count() < 2) {
            $this->command->error('請先執行 RandomPostSeeder 與 AgentApplicationSeeder，至少需要 2 位代購人才能建立請託單報價測試資料。');
            return $buyer; // 即使無法建立情境，依然回傳 buyer 以供後續排除
        }

        $this->deleteExistingScenarioData($buyer);

        $agentPool = $agents->values();
        $this->ensureActiveLogisticsForAgents($agentPool);
        $scenarioBaseDate = Carbon::now()->startOfDay();

        $scenarios = [
            [
                'status' => 'editing',
                'title' => '日本藥妝請託單',
                'country' => 'jp',
                'store_name' => '松本清 新宿三丁目店',
                'detail_address' => '東京都新宿區新宿3丁目',
                'deadline' => $scenarioBaseDate->copy()->addDays(14),
                'note' => '測試用草稿：請託人尚未送出，代購人不應在大廳看到此單。',
                'items' => [
                    ['name' => '合利他命 EX Plus 270錠', 'quantity' => 1, 'expected_price' => 1550],
                    ['name' => '大正感冒藥 44包', 'quantity' => 2, 'expected_price' => 380],
                ],
            ],
            [
                'status' => 'pending',
                'title' => '韓國美妝請託單',
                'country' => 'kr',
                'store_name' => 'Olive Young 明洞旗艦店',
                'detail_address' => '首爾市中區明洞路',
                'deadline' => $scenarioBaseDate->copy()->addDays(10),
                'note' => '測試用等待報價：目前沒有任何報價。',
                'items' => [
                    ['name' => '魔女工廠卸妝油 200ml', 'quantity' => 2, 'expected_price' => 480],
                    ['name' => '保濕修護面膜 10片', 'quantity' => 3, 'expected_price' => 420],
                ],
            ],
            [
                'status' => 'offered',
                'title' => '美國保健品請託單',
                'country' => 'us',
                'store_name' => 'CVS Pharmacy',
                'detail_address' => 'Los Angeles, CA',
                'deadline' => $scenarioBaseDate->copy()->addDays(12),
                'note' => '測試用已報價：應有 2～4 位代購人報價，尚未選定代購人。',
                'quote_count' => min(4, $agentPool->count()),
                'accepted_quote_index' => null,
                'items' => [
                    ['name' => 'Move Free 益節葡萄糖胺', 'quantity' => 2, 'expected_price' => 1250],
                    ['name' => 'Nature Made 魚油膠囊', 'quantity' => 1, 'expected_price' => 680],
                ],
            ],
            [
                'status' => 'arrivaled',
                'title' => '美國零食與包包請託單',
                'country' => 'us',
                'store_name' => 'Trader Joe\'s',
                'detail_address' => 'New York, NY',
                'deadline' => $scenarioBaseDate->copy()->addDays(4),
                'note' => '測試用已到貨：代購人已標記到貨，等待請購人確認完成。',
                'quote_count' => min(2, $agentPool->count()),
                'accepted_quote_index' => 0,
                'accepted_quote_status' => 'arrivaled',
                'items' => [
                    ['name' => 'Trader Joe\'s 帆布托特包', 'quantity' => 1, 'expected_price' => 450],
                    ['name' => '花生醬夾心餅乾', 'quantity' => 3, 'expected_price' => 220],
                ],
            ],
            [
                'status' => 'expired',
                'title' => '日本零食請託單',
                'country' => 'jp',
                'store_name' => '日本便利商店',
                'detail_address' => '東京市區',
                'deadline' => $scenarioBaseDate->copy()->subDays(2),
                'note' => '測試用已過期：截止日已過，狀態為 expired。',
                'items' => [
                    ['name' => 'Calbee 薯條三兄弟', 'quantity' => 4, 'expected_price' => 180],
                    ['name' => 'Pure 鮮果實軟糖', 'quantity' => 2, 'expected_price' => 150],
                ],
            ],
        ];

        foreach ($scenarios as $index => $scenario) {
            $requestList = $this->createRequestListScenario($buyer, $scenario, $index);
            $items = $requestList->items()->get();

            if (! empty($scenario['quote_count'])) {
                $this->createQuotesForScenario(
                    requestList: $requestList,
                    items: $items,
                    agents: $agentPool,
                    quoteCount: (int) $scenario['quote_count'],
                    acceptedQuoteIndex: $scenario['accepted_quote_index'],
                    acceptedQuoteStatus: $scenario['accepted_quote_status'] ?? 'accepted',
                );
            }
        }

        $this->command->info('成功建立郭庭瑋請託單情境！');
        return $buyer;
    }

    /**
     * ==========================================
     * 2. 建立隨機情境 (適用於大廳與所有使用者)
     * ==========================================
     */
    private function generateRandomRequests(?int $excludeBuyerId = null): void
    {
        // 抓取所有模擬的使用者，並排除前述的郭庭瑋及 ID=1 的管理員
        $query = User::where('id', '>', 1);
        if ($excludeBuyerId) {
            $query->where('id', '!=', $excludeBuyerId);
        }
        $users = $query->get();

        if ($users->isEmpty()) {
            $this->command->error("找不到其他使用者，請確認之前的 Seeder 是否已執行。");
            return;
        }

        $countryPool = [
            'jp' => [
                'titles' => ['想買日本藥妝與動漫周邊', '東京限定零食伴手禮代購', '日本連鎖藥妝店許願清單', '秋葉原限定動漫模型'],
                'stores' => ['唐吉訶德 澀谷店', '心齋橋松本清', 'Bic Camera 新宿店', '秋葉原 Animate'],
                'addresses' => ['東京都澀谷區宇田川町', '大阪市中央區心齋橋筋', '東京都新宿區新宿3丁目', '東京都千代田區外神田'],
                'item_bank' => ['合利他命 EX Plus', '大正感冒藥', 'Wakamoto 若元錠', '一蘭拉麵外帶包', '高絲美白面膜', '皮卡丘絨毛玩偶', '限定宇治抹茶餅乾']
            ],
            'kr' => [
                'titles' => ['韓國東大門服飾服裝代購', '首爾潮流美妝與 K-POP 周邊', '網紅推薦南大門飾品', '弘大設計師品牌服飾'],
                'stores' => ['東大門 DDP 商圈', '明洞 Olive Young 旗艦店', '江南大創', '弘大 Gentle Monster'],
                'addresses' => ['首爾市中區乙支路', '首爾市江南區新沙洞', '首爾市麻浦區西橋洞', '首爾市鐘路區'],
                'item_bank' => ['NewJeans 官方專輯', '應援燈棒', '魔女工廠精華液', '正官庄高麗蔘膏', '潮流刺繡大學T', '平價保濕面膜粉', '韓國辣雞麵限定版']
            ],
            'us' => [
                'titles' => ['美國 Outlet 時尚精品代購', '美國知名品牌維他命保養品', '美式潮流球鞋代購', 'Trader Joes 特色零食'],
                'stores' => ['Woodbury Common Premium Outlets', 'Trader Joes 門市', 'CVS Pharmacy', 'Flight Club 球鞋店'],
                'addresses' => ['紐約州中央山谷區', '加州洛杉磯日落大道', '內華達州拉斯維加斯', '德州休士頓'],
                'item_bank' => ['Coach 經典防刮手拿包', 'Move Free 益節葡萄糖胺', 'Nature Made 魚油膠囊', 'Nike Air Force 1 限定色', 'Trader Joes 托特包', '網紅款保溫杯', 'MAC 經典霧面口紅']
            ],
            'gb' => [
                'titles' => ['英國精品與頂級經典紅茶', '英倫復古馬汀鞋代購', '精品大牌折扣季連線', '英國知名香氛保養'],
                'stores' => ['Harrods 百貨', 'Fortnum & Mason 總店', 'Burberry 倫敦旗艦店', 'Jo Malone 專櫃'],
                'addresses' => ['倫敦騎士橋', '倫敦皮卡迪利街', '倫敦攝政街', '曼徹斯特市中心'],
                'item_bank' => ['Fortnum & Mason 皇家紅茶', 'Jo Malone 英國梨與小蒼蘭香水', 'Dr. Martens 1461 三孔馬汀鞋', 'Burberry 經典格紋圍巾', 'Dyson 國際電壓美髮梳', '劍橋包經典皮革款']
            ]
        ];

        $notesPool = [
            '希望能找近期會去當地的代購幫忙，謝謝！',
            '需要附上實體店面的購買收據或發票影本。',
            '外包裝請盡量保持完整，不要壓傷。',
            '如果遇到該款式缺貨，可以傳照片跟我討論替代顏色。',
            '行李箱空間有限的話，紙盒可以拆開壓扁沒關係。',
            '只要是正品就行，非常急需！'
        ];

        foreach ($users as $user) {
            $randomCountryCode = array_rand($countryPool);
            $scenario = $countryPool[$randomCountryCode];

            $randomTitle = $scenario['titles'][array_rand($scenario['titles'])];
            $randomStore = $scenario['stores'][array_rand($scenario['stores'])];
            $randomAddress = $scenario['addresses'][array_rand($scenario['addresses'])];
            $randomNote = $notesPool[array_rand($notesPool)];
            $randomDeadline = Carbon::now()->addDays(rand(2, 25));

            $requestList = RequestList::create([
                'user_id'        => $user->id,
                'title'          => $randomTitle,
                'country'        => $randomCountryCode,
                'store_name'     => $randomStore,
                'detail_address' => $randomAddress,
                'deadline'       => $randomDeadline,
                'note'           => $randomNote,
                'status'         => 'pending',
                'budget_total'   => 0,
                'currency'       => 'TWD',
            ]);

            $totalBudget = 0;
            $itemCount = rand(1, 3);
            $shuffledItems = $scenario['item_bank'];
            shuffle($shuffledItems);
            
            $selectedItems = array_slice($shuffledItems, 0, $itemCount);

            foreach ($selectedItems as $itemName) {
                $qty = rand(1, 5);
                $unitPrice = rand(150, 2500); 
                $itemSubtotal = $unitPrice * $qty;
                $totalBudget += $itemSubtotal;

                // 配合固定情境的資料表設計，使用 expected_price
                $requestList->items()->create([
                    'name'           => $itemName,
                    'quantity'       => $qty,
                    'expected_price' => $unitPrice,
                ]);
            }

            $requestList->update([
                'budget_total' => $totalBudget > 0 ? $totalBudget : rand(500, 8000)
            ]);
        }

        $this->command->info("成功模擬 " . $users->count() . " 筆完全隨機、跨國、多樣化商品的真實請購單！");
    }

    /**
     * ==========================================
     * 3. Helper Functions (供情境建立使用)
     * ==========================================
     */
    private function deleteExistingScenarioData(User $buyer): void
    {
        RequestList::withTrashed()
            ->where('user_id', $buyer->id)
            ->where('title', 'like', '【測試】%')
            ->forceDelete();
    }

    private function createRequestListScenario(User $buyer, array $scenario, int $index): RequestList
    {
        $budgetTotal = collect($scenario['items'])->sum(function (array $item) {
            return (float) $item['expected_price'] * (int) $item['quantity'];
        });

        $requestList = RequestList::create([
            'user_id' => $buyer->id,
            'title' => $scenario['title'],
            'country' => $scenario['country'],
            'store_name' => $scenario['store_name'],
            'detail_address' => $scenario['detail_address'],
            'deadline' => $scenario['deadline'],
            'note' => $scenario['note'],
            'status' => $scenario['status'],
            'budget_total' => $budgetTotal,
            'currency' => 'TWD',
            'expired_notified_at' => $scenario['status'] === 'expired' ? Carbon::now()->subDay() : null,
            'created_at' => Carbon::now()->subDays(8 - $index),
            'updated_at' => Carbon::now()->subDays(max(0, 7 - $index)),
        ]);

        foreach ($scenario['items'] as $item) {
            $requestList->items()->create([
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'expected_price' => $item['expected_price'],
                'reference_url' => 'https://example.com/test-request/' . $requestList->id,
                'specification' => 'Seeder 測試資料：' . $scenario['title'],
            ]);
        }

        return $requestList;
    }

    /**
     * 郭庭瑋固定情境中的請託單若有代購人報價，該代購人必須至少有一筆啟用中的物流設定。
     */
    private function ensureActiveLogisticsForAgents($agents): void
    {
        foreach ($agents as $agent) {
            $hasActiveLogistics = Logistics::where('user_id', $agent->id)
                ->where('status', true)
                ->exists();

            if ($hasActiveLogistics) {
                continue;
            }

            Logistics::create([
                'user_id' => $agent->id,
                'name' => '測試宅配物流',
                'status' => true,
                'ship_type' => '宅配',
                'payment_method' => '線上付款',
                'available_times' => ['全週'],
                'temp_layer' => '常溫',
            ]);
        }
    }

    private function createQuotesForScenario(
        RequestList $requestList,
        $items,
        $agents,
        int $quoteCount,
        ?int $acceptedQuoteIndex,
        string $acceptedQuoteStatus,
    ): void {
        $selectedAgents = $agents->take($quoteCount)->values();
        $acceptedQuote = null;

        foreach ($selectedAgents as $quoteIndex => $agent) {
            $quoteStatus = $acceptedQuoteIndex === $quoteIndex
                ? $acceptedQuoteStatus
                : ($acceptedQuoteIndex === null ? 'pending' : 'rejected');

            $quoteTotal = 0;
            $quote = Quote::create([
                'request_list_id' => $requestList->id,
                'user_id' => $agent->id,
                'price' => 0,
                'estimated_date' => Carbon::now()->addDays(5 + $quoteIndex),
                'comment' => "Seeder 測試報價：{$agent->name} 提供第 " . ($quoteIndex + 1) . ' 版報價。',
                'status' => $quoteStatus,
                'created_at' => Carbon::now()->subDays(3)->addHours($quoteIndex),
                'updated_at' => Carbon::now()->subDays(2)->addHours($quoteIndex),
            ]);

            foreach ($items as $item) {
                $unitPrice = max(50, (float) ($item->expected_price ?? 300) + ($quoteIndex * 80) + rand(-30, 60));
                $quoteTotal += $unitPrice * (int) $item->quantity;

                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'request_item_id' => $item->id,
                    'unit_price' => $unitPrice,
                ]);
            }

            $quote->update(['price' => $quoteTotal]);

            if ($acceptedQuoteIndex === $quoteIndex) {
                $acceptedQuote = $quote;
            }
        }

        if ($acceptedQuote) {
            $requestList->update([
                'people' => $acceptedQuote->user_id,
                'agent_quote_total' => $acceptedQuote->price,
            ]);
        }
    }
}