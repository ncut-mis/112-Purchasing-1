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
        // 1. 抓取通過審核的 5 筆代購人
        $agents = User::whereHas('agentApplication', function($query) {
            $query->where('status', 'approved');
        })->get();

        if ($agents->isEmpty()) {
            $this->command->error("找不到狀態為 approved 的代購人，請確認 AgentApplicationSeeder 是否先執行。");
            return;
        }

        // 2. 開始為這 5 位代購人產生貼文
        foreach ($agents as $user) {
            // 每人隨機發 2 則貼文
            for ($i = 0; $i < 2; $i++) {
                $post = AgentPost::create([
                    'user_id'                 => $user->id,
                    'title'                   => '【專業代購】來自 ' . ($i % 2 == 0 ? '日本' : '韓國') . ' 的精選好物',
                    'country'                 => $i % 2 == 0 ? '日本' : '韓國',
                    'description'             => '這是由模擬系統自動產生的專業代購貼文內容。',
                    'start_date'              => Carbon::now(),
                    'end_date'                => Carbon::now()->addDays(7),
                    'estimated_shipping_date' => Carbon::now()->addDays(14), // 補上模型中的欄位
                    'status'                  => 'open', // 設為 open 確保網頁能顯示
                    'cover_image'             => null,
                ]);

                // 3. 建立關聯商品，確保貼文不是空的
                $post->products()->create([
                    'name'         => '模擬熱銷商品 ' . ($i + 1),
                    'price'        => rand(100, 2000),
                    'max_quantity' => rand(5, 50),
                    'currency'     => 'TWD',
                    'is_active'    => true,
                    'image_path'   => null,
                ]);
            }
        }

        $this->command->info("成功利用 5 位代購人發布了 " . ($agents->count() * 2) . " 則貼文。");
    }
}