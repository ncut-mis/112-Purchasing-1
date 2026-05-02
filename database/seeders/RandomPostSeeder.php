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
                'name' => '代購小幫手',
                'password' => $fixedPassword
            ]
        );

        // 2. 產生 50 個隨機使用者
        $users = User::factory(50)->create([
            'password' => $fixedPassword,
            'role'     => 'seller', // 讓他們直接擁有代購權限
        ]);



    
    }
}
