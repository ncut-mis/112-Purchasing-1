<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\RequestList;
use Carbon\Carbon;

class RequestListSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 抓取所有模擬的使用者
        $users = User::where('id', '>', 1)->get();

        if ($users->isEmpty()) {
            $this->command->error("找不到使用者，請確認之前的 Seeder 是否已執行。");
            return;
        }

        // 🎯 2. 準備超豐富的多國隨機情境池與商品庫
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
            ],
            'th' => [
                'titles' => ['泰國曼谷熱門零食與香氛', '泰國設計師精品包包', '恰圖恰市集好物許願', '泰式傳統香料連線'],
                'stores' => ['恰圖恰週末市集', 'Big C 總店', 'Central World 商場', '建興酒家周邊伴手禮店'],
                'addresses' => ['曼谷帕歌區帕混育清路', '曼谷巴吞旺區紅十字路', '曼谷孔堤區', '清邁古城區'],
                'item_bank' => ['Bento 超辣魷魚片', '小老闆海苔巨無霸包', 'Counterpain 酸痛軟膏', '泰國皇家蜂蜜', '手標牌泰式奶茶粉', '絲綢刺繡抱枕', '香氛精油擴香組']
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

        // 3. 遍歷每個人發出需求單
        foreach ($users as $user) {
            // 隨機選一個國家代碼 (jp, kr, us, gb, th)
            $randomCountryCode = array_rand($countryPool);
            $scenario = $countryPool[$randomCountryCode];

            // 從該國家各自隨機抽題
            $randomTitle = $scenario['titles'][array_rand($scenario['titles'])];
            $randomStore = $scenario['stores'][array_rand($scenario['stores'])];
            $randomAddress = $scenario['addresses'][array_rand($scenario['addresses'])];
            $randomNote = $notesPool[array_rand($notesPool)];

            // 隨機決定截止日期（從 2 天後的超急單，到 25 天後的寬裕單）
            $randomDeadline = Carbon::now()->addDays(rand(2, 25));

            // 建立需求單主檔 (先給 budget_total = 0，稍後計算)
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

            // 🎯 4. 動態建立隨機個數的商品項目（隨機挑選 1 ~ 4 樣不重複商品）
            $totalBudget = 0;
            $itemCount = rand(1, 4);
            $shuffledItems = $scenario['item_bank'];
            shuffle($shuffledItems); // 打亂該國商品庫
            
            $selectedItems = array_slice($shuffledItems, 0, $itemCount); // 切出隨機數量的商品

            foreach ($selectedItems as $itemName) {
                $qty = rand(1, 5); // 隨機數量 1 ~ 5
                
                // 假設你的 RequestItem 資料表有單價或價格欄位（例如價格單價 price）
                // 這裡我們隨機產出一個合情合理的單價
                $unitPrice = rand(150, 2500); 
                $itemSubtotal = $unitPrice * $qty;
                $totalBudget += $itemSubtotal;

                $requestList->items()->create([
                    'name'     => $itemName,
                    'quantity' => $qty,
                    // 'price' => $unitPrice, // 👈 如果你的項目表有單價欄位可以取消註解這行
                ]);
            }

            // 🎯 5. 計算完所有隨機商品的總價後，回填更新主表的預算總額
            // 如果你的系統目前沒有強制算總價，這裡就純粹塞一個隨機總預算數值
            $requestList->update([
                'budget_total' => $totalBudget > 0 ? $totalBudget : rand(500, 8000)
            ]);
        }

        $this->command->info("成功模擬 " . $users->count() . " 筆完全隨機、跨國、多樣化商品的真實請購單！");
    }
}