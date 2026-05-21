<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;

class RandomPostSeeder extends Seeder
{
    public function run(): void
    {
        // 先加密好密碼，提高執行效率
        $fixedPassword = bcrypt('12345678');

        // 1. 建立固定測試帳號 (確保你自己能登入)
        $testUser = User::updateOrCreate(
            ['email' => 'helper@example.com'],
            [
                'name' => '測試員',
                'password' => $fixedPassword
            ]
        );

        // 🎯 中文姓名隨機生成庫
        $lastNames = ['陳', '林', '黃', '張', '李', '王', '吳', '劉', '蔡', '賴', '楊', '侯', '許', '鄭', '謝', '洪', '郭', '邱', '曾', '廖','余','丁'];
        $firstNames = ['冠宇', '宗翰', '家豪', '彥廷', '承翰', '宇軒', '豪傑', '俊傑', '威廷', '冠廷', '雅婷', '欣妤', '詩婷', '妤婷', '子晴', '詠晴', '美玲', '婷婷', '鈺婷', '佳穎', '志明', '春嬌', '書豪', '建宏', '俊宏'];

        // 2. 產生 50 個隨機使用者 (將名字置換成中文)
        for ($i = 0; $i < 50; $i++) {
            // 隨機組合姓氏與名字
            $randomChineseName = $lastNames[array_rand($lastNames)] . $firstNames[array_rand($firstNames)];

            User::factory()->create([
                'name'     => $randomChineseName, // 🎯 覆蓋為中文名字
                'password' => $fixedPassword,
                'role'     => 'seller', // 讓他們直接擁有代購權限
            ]);
        }
    }
}