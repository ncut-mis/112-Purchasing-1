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

        // 2. 準備情境池
        $scenarios = [
            [
                'title' => '想買日本藥妝：合利他命與感冒藥',
                'country' => '日本',
                'city' => '大阪',
                'note' => '希望能找在心齋橋附近的代購。',
                'items' => ['合利他命 EX Plus', '大正感冒藥'] // 預計建立的商品
            ],
            [
                'title' => '韓國限定 K-POP 周邊代購',
                'country' => '韓國',
                'city' => '首爾',
                'note' => '需要附上實體店面的購買收據影本。',
                'items' => ['NewJeans 專輯', '應援燈棒']
            ]
        ];

        // 3. 遍歷每個人發出需求單
        foreach ($users as $user) {
            $data = $scenarios[array_rand($scenarios)];

            // 建立需求單主檔
            $requestList = RequestList::create([
                'user_id'      => $user->id,
                'title'        => $data['title'],
                'country'      => $data['country'],
                'city'         => $data['city'],
                'deadline'     => Carbon::now()->addDays(rand(7, 21)),
                'note'         => $data['note'],
                'status'       => 'pending',
                'budget_total' => 0,
                'currency'     => 'TWD',
            ]);

            // 4. 重要：建立該需求單對應的商品項目
            // 這裡假設你的關聯模型叫做 items()，且模型是 RequestItem
            foreach ($data['items'] as $itemName) {
                $requestList->items()->create([
                    'name'         => $itemName,
                    'quantity'     => rand(1, 3),
                    
                ]);
            }
        }

        $this->command->info("成功讓 " . $users->count() . " 位使用者發出了包含商品的需求清單！");
    }
}