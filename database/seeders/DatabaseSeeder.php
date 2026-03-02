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
        $user = User::first() ?? User::factory()->create([
            'name' => '代購小幫手',
            'email' => 'helper@example.com',
        ]);

        // 2. 定義一些隨機的代購資料庫
        $titles = [
            '【日本連線】早春限定櫻花杯與藥妝代購',
            '【韓國直送】最新 K-POP 周邊與服飾連線',
            '【泰國代購】曼谷限定大象杯與手標泰奶',
            '【歐洲精品】巴黎連線 LV/Chanel 經典款代購',
            '【美國代購】Black Friday 運動品牌鞋款特賣'
        ];

        $regions = ['日本', '韓國', '泰國', '法國', '美國'];

        // 3. 隨機挑選一組資料新增
        $randomIndex = array_rand($titles);

        Post::create([
            'user_id' => $user->id,
            'title' => $titles[$randomIndex],
            'region' => $regions[$randomIndex],
            'description' => '這是一則自動產生的範例貼文。我們提供專業的海外代購服務，商品皆為親自採買，保證正品。歡迎透過私訊或許願池與我們聯繫細節！',
            'max_quantity' => rand(10, 50),
            'deadline' => Carbon::now()->addDays(rand(5, 14)),
            'status' => 'active',
        ]);

        echo "成功新增一筆隨機代購貼文！\n";
    }
}