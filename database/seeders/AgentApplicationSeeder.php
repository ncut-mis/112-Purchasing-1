<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AgentApplication;

class AgentApplicationSeeder extends Seeder
{
    public function run(): void
    {
        // 抓取現有的使用者（跳過管理員，假設 ID 1 是管理員）
        $users = User::where('id', '>', 1)->take(20)->get();

        if ($users->isEmpty()) {
            $users = User::factory()->count(20)->create();
        }

        $countries = ['日本', '韓國', '美國', '泰國', '中國'];

        foreach ($users as $index => $user) {
            // 🎯 【新增核心邏輯】隨機產生該使用者的可代購國家（1 ~ 2 個國家）
            $numCount = rand(1, 2); // 隨機決定要給 1 個還是 2 個國家
            $randomKeys = (array) array_rand($countries, $numCount); // 隨機抓出索引值，強制轉型成陣列
            
            // 透過索引值拿到國家名稱
            $selectedCountries = [];
            foreach ($randomKeys as $key) {
                $selectedCountries[] = $countries[$key];
            }
            
            // 🎯 變成中文字串，用逗號隔開（例如："日本,美國"）
            // 如果你的系統習慣用 JSON 字串，也可以改成 json_encode($selectedCountries, JSON_UNESCAPED_UNICODE)
            $purchasableCountriesString = json_encode($selectedCountries);

            // 建立申請紀錄
            AgentApplication::create([
                'user_id'        => $user->id,
                'name'           => $user->name . '（系統直升）',
                'country'        => $countries[$index % count($countries)],
                'phone'          => '09' . rand(10000000, 99999999),
                'main_region'    => $countries[$index % count($countries)] . '地區代購',
                'experience'     => '資深代購經驗',
                'id_number'      => 'A123456789',
                'id_image_front' => 'identities/front.jpg',
                'id_image_back'  => 'identities/back.jpg',
                'status'         => 'approved', // 直接設為已通過
            ]);

            // 重點：直接更新 User 資料表的身分與可代購國家欄位
            $user->update([
                'role'                  => 'seller', 
                'purchasable_countries' => $purchasableCountriesString, // 🎯 寫入生成好的中文字串
            ]);
        }

        echo "成功模擬 20 筆代購申請資料，並已同步更新 User 的可代購國家！\n";
    }
}