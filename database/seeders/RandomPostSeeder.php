<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;

class RandomPostSeeder extends Seeder
{
    /**
     * 執行資料庫種子。
     */
    public function run(): void
    {
        // 1. 確保資料庫裡至少有一個使用者（代購人）
        // 如果還沒有使用者，就建立一個預設的
        $user = User::first() ?? User::factory()->create([
            'name' => '專業代購員',
            'email' => 'agent@example.com',
        ]);

        // 2. 定義多樣化的代購貼文範本
        $templates = [
            ['title' => '【日本連線】東京限定吉伊卡哇、藥妝代購', 'region' => '日本'],
            ['title' => '【韓國連線】最新 K-POP 周邊與設計師服飾', 'region' => '韓國'],
            ['title' => '【泰國連線】曼谷手標泰奶、限定零食代購', 'region' => '泰國'],
            ['title' => '【歐洲精品】巴黎/倫敦連線精品包款代購', 'region' => '法國'],
            ['title' => '【美國代購】Outlet 特賣運動鞋、名牌飾品', 'region' => '美國'],
            ['title' => '【澳洲連線】天然保養品、保養品特輯', 'region' => '澳洲'],
        ];

        // 3. 隨機生成 5 筆貼文
        for ($i = 0; $i < 5; $i++) {
            $template = $templates[array_rand($templates)];
            
            Post::create([
                'user_id'      => $user->id,
                'title'        => $template['title'] . ' #' . rand(100, 999),
                'region'       => $template['region'],
                'description'  => '這是一則自動產生的代購貼文。目前正在當地進行連線，歡迎私訊諮詢商品細節。保證正品，下單後提供採買證明！',
                'max_quantity' => rand(5, 100),
                'deadline'     => Carbon::now()->addDays(rand(3, 20)),
                'status'       => 'active',
            ]);
        }

        $this->command->info('成功新增 5 筆隨機代購貼文！');
    }
}