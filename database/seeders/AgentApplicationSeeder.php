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

            // 重點：直接更新 User 資料表的身分 (假設你的欄位是 role)
            $user->update([
                'role' => 'seller', // 或是 'agent'，依據你的資料庫設計
            ]);
        }

        echo "成功模擬 20 筆代購申請資料！\n";
    }
}