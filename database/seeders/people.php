<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgentPost;
use App\Models\User;
use Carbon\Carbon;

class people extends Seeder
{
    public function run(): void
    {
        // 1. 抓取通過審核的代購人
        $agents = User::whereHas('agentApplication', function($query) {
            $query->where('status', 'approved');
        })->get();

        if ($agents->isEmpty()) {
            $this->command->error("找不到狀態為 approved 的代購人，請確認 AgentApplicationSeeder 是否先執行。");
            return;
        }

        // 🎯 2. 準備豐富的跨國開團文案與熱銷商品庫（確保包含所有可能出現的國家）
        $campaignPool = [
            '日本' => [
                'titles' => ['【日本現場連線】限時藥妝保健與春季限定櫻花周邊空運團！', '【動漫聖地巡禮】秋葉原/池袋現場限定模型、精品週邊盲盒代購', '【東京伴手禮大集合】紐約起司脆餅、伴手禮排隊名店即時開團'],
                'description' => '下週即將出發日本東京！預計會親自前往新宿松本清與澀谷唐吉訶德採購。所有商品一律附上實體購買憑證照片，保證全新正品！入單請早，行李箱空間有限滿單即止。',
                'products' => [
                    ['name' => '合利他命 EX PLUS 270粒裝', 'price' => 1650],
                    ['name' => '大正百保能感冒微粒 44包', 'price' => 380],
                    ['name' => 'NEW YORK PERFECT CHEESE 起司捲 (12入)', 'price' => 620],
                    ['name' => '任天堂官方限定 瑪利歐/皮卡丘造型吊飾', 'price' => 450],
                    ['name' => '近江兄弟高保濕護唇膏 兩入組', 'price' => 120]
                ]
            ],
            '韓國' => [
                'titles' => ['【東大門歐美風】本週最新流行服飾、設計師品牌檔口親自挑選連線', '【K-POP 週邊爆買】知名韓星應援燈棒、專輯限定特典代購團', '【明洞美妝強襲】Olive Young 暢銷榜面膜、保養品現場限時囤貨團'],
                'description' => '長期駐點韓國首爾！這次主要幫大家跑東大門服飾檔口與明洞各大美妝旗艦店。有需要補貨保養品或追最新單曲專輯、應援周邊的朋友千萬別錯過，預計回台後三天內出貨完畢！',
                'products' => [
                    ['name' => 'Olive Young 補水修護面膜 (10片裝)', 'price' => 420],
                    ['name' => '韓國設計師品牌 刺繡棉質寬鬆大學T', 'price' => 1380],
                    ['name' => '正官庄 高麗蔘高麗人蔘精華口服液', 'price' => 1950],
                    ['name' => '最新熱門男女天團 官方限定版全紀錄專輯', 'price' => 550],
                    ['name' => '魔女工廠 溫和清爽卸妝油 200ml', 'price' => 480]
                ]
            ],
            '美國' => [
                'titles' => ['【歐美精品直營】美國精品特價黑五折扣連線！皮夾包包超殺優惠', '【健康生活補給】好市多/CVS 維他命、MoveFree 關節靈強效團', '【美式潮流】西岸限定球鞋、街頭潮流服飾極速空運連線'],
                'description' => '目前正在美國加州 Outlet 現場巡場！各大精品品牌（Coach, Michael Kors, Kate Spade）正在進行限時折扣，折扣非常驚人。所有皮件皆附購買禮物收據（Gift Receipt）及防塵袋，敬請安心下單！',
                'products' => [
                    ['name' => 'Coach 經典防刮皮革防盜雙拉鍊手拿包', 'price' => 1890],
                    ['name' => 'Move Free 益節加強型葡萄糖胺 (75錠)', 'price' => 980],
                    ['name' => 'Trader Joes 經典刺繡棉麻時尚托特包', 'price' => 290],
                    ['name' => 'Kiehls 契爾氏金盞花植物精華化妝水 500ml', 'price' => 1680],
                    ['name' => 'Tommy Hilfiger 經典小 Logo 百搭短T', 'price' => 750]
                ]
            ],
            '英國' => [
                'titles' => ['【英倫午茶時光】Fortnum & Mason 皇家御用紅茶、經典經典司康抹醬團', '【精品大牌代購】倫敦攝政街/Harrods百貨 春季折扣連線代購', '【英式古典香氛】Jo Malone 經典系列香水、祖馬龍擴香連線'],
                'description' => '出差英國倫敦期間順便開團！將會親自走訪 Fortnum & Mason 總店為各位挑選茶葉。英倫精品折扣季同步連線中，有需要代買皮鞋、風衣或英倫香氛、保養品的朋友歡迎許願委託！',
                'products' => [
                    ['name' => 'Fortnum & Mason 英國皇家經典伯爵茶 (茶葉罐裝 125g)', 'price' => 680],
                    ['name' => 'Jo Malone 英國梨與小蒼蘭香水 30ml', 'price' => 1980],
                    ['name' => 'The Body Shop 美體小舖生薑頭皮調理洗髮精 400ml', 'price' => 450],
                    ['name' => 'Evelyn 瑰棒翠經典護手霜 經典香氛三入組', 'price' => 590]
                ]
            ],
            '泰國' => [
                'titles' => ['【曼谷瘋開團】Big C 超人氣零食、Bento辣魷魚、泰式手標奶茶連線', '【泰式療癒香氛】曼谷網網推崇天然精油擴香、草本青草按摩膏', '【泰國設計精品】平價版三宅一生精品包、手工度假風編織包物美價廉'],
                'description' => '曼谷度假順便幫大家爆買伴手禮！手標牌紅茶、各式網紅零食皆可在這團一次購齊。會特別前往 Big C 大型超市掃貨，打包時會妥善使用氣泡布包裝，絕對不讓你的零食碎成粉，快來下單吧！',
                'products' => [
                    ['name' => '手標牌泰式奶茶專用紅茶粉 (罐裝 450g)', 'price' => 150],
                    ['name' => 'Bento 泰國超特辣香脆魷魚片 (12包入大包裝)', 'price' => 180],
                    ['name' => '泰國皇家純天然蜂蜜 (便利擠壓條裝)', 'price' => 95],
                    ['name' => '曼谷 Counterpain 溫熱型肌肉酸痛軟膏 120g', 'price' => 220],
                    ['name' => 'BKK Original 泰國特色經典印花手提鏈條包', 'price' => 350]
                ]
            ],
            // 🎯【補齊中國情境資料】避免找不到陣列 Key 噴錯
            '中國' => [
                'titles' => ['【中國淘寶/天貓】換季潮流服飾、網紅推薦高 CP 值彩妝爆款連線團', '【零嘴大作戰】魔芋爽、三隻松鼠堅果、螺螄粉零食限定集運團', '【古風文創】故宮淘寶文創周邊、古風手帳文具許願代購團'],
                'description' => '專業中國電商與實體代購連線！不管是淘寶、天貓上的換季服飾，還是最熱門的各種追劇零嘴零食，這團通通幫你打包運回台！海運/空運定期回台，包裹全程物流可追蹤，有任何想買的歡迎點擊委託！',
                'products' => [
                    ['name' => '衛龍魔芋爽 香辣風味 (20包盒裝)', 'price' => 199],
                    ['name' => '三隻松鼠 每日堅果大禮包 750g', 'price' => 450],
                    ['name' => '好歡螺 經典原味柳州螺螄粉 (300g*3袋)', 'price' => 360],
                    ['name' => '故宮文創 限量古風刺繡書籤禮盒', 'price' => 280],
                    ['name' => '完美日記 十二色動物眼淚眼影盤', 'price' => 520]
                ]
            ]
        ];

        $fallbackCountries = ['日本', '韓國', '美國', '英國', '泰國', '中國'];

        $titleSuffixPool = [
            '本週第一團', '限時連線', '現場直送', '快閃補貨', '熱門加開',
            '空運優先', '晚鳥加碼', '人氣精選', '行李箱直送', '實拍回報',
            '清單代買', '折扣季限定', '會員回饋', '精品快線', '零食專場',
            '藥妝特輯', '香氛專場', '服飾熱賣', '球鞋連線', '伴手禮團',
        ];
        $titleCursor = 0;

        // 3. 開始為每位代購人產生貼文
        foreach ($agents as $user) {
            
            $myCountriesString = trim((string)$user->purchasable_countries);
            
            if ($myCountriesString !== '') {
                $myCountries = explode(',', $myCountriesString);
            } else {
                $myCountries = (array) array_rand(array_flip($fallbackCountries), 2);
            }

            // 每位代購人隨機發 2 則貼文
            for ($i = 0; $i < 2; $i++) {
                $chosenCountry = $myCountries[array_rand($myCountries)];
                
                // 🎯【核心安全防呆】：如果萬一資料庫的國家名稱在 Pool 裡找不到，自動 Fallback 到日本
                if (!array_key_exists($chosenCountry, $campaignPool)) {
                    $chosenCountry = '日本';
                }

                $campaign = $campaignPool[$chosenCountry];

                // 從該國家情境中抽取標題
                $randomTitle = $campaign['titles'][array_rand($campaign['titles'])];

                $titleSuffix = $titleSuffixPool[$titleCursor % count($titleSuffixPool)] . '-' . str_pad((string) ($titleCursor + 1), 2, '0', STR_PAD_LEFT);
                $titleCursor++;
                $finalTitle = "{$randomTitle}｜{$titleSuffix}";

                // 隨機決定開團時間
                $startDate = Carbon::now()->subDays(rand(0, 2));
                $endDate = Carbon::now()->addDays(rand(3, 10));
                $shippingDate = Carbon::instance($endDate)->addDays(rand(7, 12));

                // 建立貼文主檔
                $post = AgentPost::create([
                    'user_id'                 => $user->id,
                    'title'                   => $finalTitle,
                    'country'                 => $chosenCountry,
                    'description'             => $campaign['description'], // 👈 這裡現在絕對不會找不到 Key 了！
                    'start_date'              => $startDate,
                    'end_date'                => $endDate,
                    'estimated_shipping_date' => $shippingDate,
                    'status'                  => 'open',
                    'cover_image'             => null,
                ]);

                // 4. 建立多樣化商品
                $productBank = $campaign['products'];
                shuffle($productBank);
                $productCount = rand(1, 3);
                $selectedProducts = array_slice($productBank, 0, $productCount);

                foreach ($selectedProducts as $prodData) {
                    $post->products()->create([
                        'name'         => $prodData['name'],
                        'price'        => $prodData['price'] + rand(-20, 50),
                        'max_quantity' => rand(10, 100),
                        'currency'     => 'TWD',
                        'is_active'    => true,
                        'image_path'   => null,
                    ]);
                }
            }
        }

        $this->command->info("成功抓取 " . $agents->count() . " 位真實權限代購人，並修復了國家 Key 缺失錯誤，順利發布貼文！");
    }
}