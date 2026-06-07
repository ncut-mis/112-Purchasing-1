<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AgentPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use App\Models\Logistics;

class people extends Seeder
{
    public function run(): void
    {
        // 1. 抓取通過審核的代購人
        $agents = User::whereHas('agentApplication', function ($query) {
            $query->where('status', 'approved');
        })->get();

        if ($agents->isEmpty()) {
            $this->command->error("找不到狀態為 approved 的代購人");
            return;
        }

        // 2. 商品與文案池
        $campaignPool = [
            '日本' => [
                'titles' => [
                    '【日本現場連線】限時藥妝保健與春季限定櫻花周邊空運團！',
                    '【動漫聖地巡禮】秋葉原/池袋現場限定模型、精品週邊盲盒代購',
                    '【東京伴手禮大集合】紐約起司脆餅、伴手禮排隊名店即時開團'
                ],
                'description' => '下週即將出發日本東京！',
                'products' => [
                    ['name' => '合利他命 EX PLUS 270粒裝', 'price' => 1650],
                    ['name' => '大正百保能感冒微粒 44包', 'price' => 380],
                    ['name' => 'NEW YORK PERFECT CHEESE 起司捲 (12入)', 'price' => 620],
                    ['name' => '任天堂官方限定 瑪利歐造型吊飾', 'price' => 450],
                    ['name' => '近江兄弟高保濕護唇膏 兩入組', 'price' => 120]
                ]
            ],
            '韓國' => [
                'titles' => [
                    '【東大門歐美風】本週最新流行服飾',
                    '【K-POP 週邊爆買】專輯限定特典代購團',
                    '【明洞美妝強襲】Olive Young 暢銷榜'
                ],
                'description' => '長期駐點韓國首爾！',
                'products' => [
                    ['name' => 'Olive Young 補水修護面膜 (10片裝)', 'price' => 420],
                    ['name' => '韓國設計師品牌 刺繡棉質寬鬆大學T', 'price' => 1380],
                    ['name' => '正官庄 高麗蔘精華口服液', 'price' => 1950]
                ]
            ],
            '美國' => [
                'titles' => [
                    '【歐美精品直營】Outlet 黑五折扣',
                    '【健康生活補給】Costco 維他命團',
                    '【美式潮流】西岸限定球鞋連線'
                ],
                'description' => '美國加州現場連線！',
                'products' => [
                    ['name' => 'Coach 防刮皮革手拿包', 'price' => 1890],
                    ['name' => 'Move Free 益節葡萄糖胺', 'price' => 980],
                    ['name' => 'Kiehls 金盞花化妝水 500ml', 'price' => 1680]
                ]
            ],
            '英國' => [
                'titles' => [
                    '【英倫午茶時光】Fortnum & Mason',
                    '【精品代購】Harrods 百貨連線',
                    '【Jo Malone 香氛團】'
                ],
                'description' => '倫敦現場代購！',
                'products' => [
                    ['name' => 'Fortnum & Mason 伯爵茶 125g', 'price' => 680],
                    ['name' => 'Jo Malone 英國梨與小蒼蘭香水 30ml', 'price' => 1980]
                ]
            ]
        ];

        $fallbackCountries = ['日本', '韓國', '美國', '英國'];

        $titleSuffixPool = [
            '本週第一團', '限時連線', '現場直送', '快閃補貨',
            '人氣精選', '精品快線', '零食專場'
        ];

        $titleCursor = 0;

        // 3. 建立貼文
        foreach ($agents as $user) {

            $myCountries = json_decode($user->purchasable_countries, true);
            if (!is_array($myCountries) || empty($myCountries)) {
                $myCountries = (array) array_rand(array_flip($fallbackCountries), 2);
            }

            $hasEnabledLogistics = Logistics::where('user_id', $user->id)
                ->where('status', true)
                ->exists();

            $postStatus = $hasEnabledLogistics ? 'open' : 'draft';
            $forceEditingForSpecialAgent = in_array($user->id, [2, 3, 4,5], true);

            for ($i = 0; $i < 2; $i++) {
                $status = ($forceEditingForSpecialAgent && $i === 0) ? 'draft' : $postStatus;

                $chosenCountry = $myCountries[array_rand($myCountries)];

                if (!isset($campaignPool[$chosenCountry])) {
                    $chosenCountry = '日本';
                }

                $campaign = $campaignPool[$chosenCountry];

                $randomTitle = $campaign['titles'][array_rand($campaign['titles'])];

                $titleSuffix = $titleSuffixPool[$titleCursor % count($titleSuffixPool)] . '-' . str_pad($titleCursor + 1, 2, '0', STR_PAD_LEFT);
                $titleCursor++;

                $post = AgentPost::create([
                    'user_id' => $user->id,
                    'title' => "{$randomTitle}｜{$titleSuffix}",
                    'country' => $chosenCountry,
                    'description' => $campaign['description'],
                    'start_date' => Carbon::now()->subDays(rand(0, 2)),
                    'end_date' => Carbon::now()->addDays(rand(3, 10)),
                    'estimated_shipping_date' => Carbon::now()->addDays(rand(10, 20)),
                    'status' => $status,
                    'cover_image' => null,
                ]);

                // 商品
                shuffle($campaign['products']);
                $selected = array_slice($campaign['products'], 0, rand(1, 3));

                foreach ($selected as $prod) {

                    $imageName = $prod['name'] . '.jpg';
                    $imageFile = resource_path("images/{$imageName}");

                    $imageData = null;

                    if (File::exists($imageFile)) {
                        $imageData = File::get($imageFile);

                        $this->command->info("✔ 找到圖片：{$imageName}");
                    } else {
                        $this->command->warn("✘ 找不到圖片：{$imageFile}");
                    }

                    $post->products()->create([
                        'name' => $prod['name'],
                        'price' => $prod['price'] + rand(-20, 50),
                        'max_quantity' => rand(10, 100),
                        'currency' => 'TWD',
                        'is_active' => true,
                        'image_path' => $imageData,
                    ]);
                }
            }
        }

        $this->command->info("Seeder 完成：已建立代購貼文 + BLOB圖片");
    }
}