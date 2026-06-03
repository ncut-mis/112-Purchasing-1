<?php

namespace Database\Seeders;

use App\Models\AgentApplication;
use App\Models\Logistics;
use Illuminate\Database\Seeder;

class AgentLogisticsSeeder extends Seeder
{
    /**
     * 為模擬出的 20 位代購人建立已啟用的物流設定。
     */
    public function run(): void
    {
        $agents = AgentApplication::query()
            ->with('user')
            ->where('status', 'approved')
            ->whereHas('user', function ($query) {
                $query->where('role', 'seller');
            })
            ->oldest('id')
            ->take(20)
            ->get()
            ->pluck('user')
            ->filter();

        if ($agents->isEmpty()) {
            $this->command->error('找不到已通過審核且具有代購人身份的會員，請確認 AgentApplicationSeeder 是否先執行。');
            return;
        }

        $carrierProfiles = [
            ['name' => '黑貓宅急便', 'ship_type' => '宅配', 'payment_method' => '貨到付款', 'temp_layer' => '常溫', 'available_times' => ['週一', '週二', '週三']],
            ['name' => '新竹物流', 'ship_type' => '宅配', 'payment_method' => '線上付款', 'temp_layer' => '常溫', 'available_times' => ['週六', '週日']],
            ['name' => '7-Eleven店到店', 'ship_type' => '超商', 'payment_method' => '線上付款', 'temp_layer' => '常溫', 'available_times' => ['全週']],
            ['name' => '全家店到店', 'ship_type' => '超商', 'payment_method' => '線上付款', 'temp_layer' => '常溫', 'available_times' => ['全週']],
            ['name' => '蝦皮店到店', 'ship_type' => '超商', 'payment_method' => '貨到付款', 'temp_layer' => '常溫', 'available_times' => ['全週']],
            ['name' => '中華郵政', 'ship_type' => '宅配', 'payment_method' => '線上付款', 'temp_layer' => '冷藏', 'available_times' => ['週二', '週五']],
            ['name' => '嘉里大榮物流', 'ship_type' => '宅配', 'payment_method' => '貨到付款', 'temp_layer' => '常溫', 'available_times' => ['週四', '週六', '週日']],
            ['name' => '宅配通', 'ship_type' => '宅配', 'payment_method' => '貨到付款', 'temp_layer' => '常溫', 'available_times' => ['週一', '週三']],
            ['name' => '全球快遞', 'ship_type' => '宅配', 'payment_method' => '線上付款', 'temp_layer' => '冷藏', 'available_times' => ['週一', '週三', '週五']],
            ['name' => '大榮貨運', 'ship_type' => '宅配', 'payment_method' => '貨到付款', 'temp_layer' => '常溫', 'available_times' => ['週二', '週四']],
        ];

        foreach ($agents as $index => $agent) {
            // 重新產生這批模擬代購人的物流，確保每位代購人最後剛好有 1～2 筆物流設定。
            Logistics::where('user_id', $agent->id)->delete();

            $logisticsCount = ($index % 2) + 1;
            $profileOffset = $index % count($carrierProfiles);

            for ($i = 0; $i < $logisticsCount; $i++) {
                $profile = $carrierProfiles[($profileOffset + $i) % count($carrierProfiles)];

                Logistics::create([
                    'user_id' => $agent->id,
                    'name' => $profile['name'],
                    'status' => rand(0, 1), // 隨機啟用或停用
                    'ship_type' => $profile['ship_type'],
                    'payment_method' => $profile['payment_method'],
                    'available_times' => $profile['available_times'],
                    'temp_layer' => $profile['temp_layer'],
                ]);
            }
        }

        $this->command->info('成功為 ' . $agents->count() . ' 位代購人建立 1～2 筆已啟用物流設定！');
    }
}
