<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\User;

class ShopController extends Controller
{
    /**
     * 顯示「找代購」列表頁面，支援搜尋功能
     */
    public function store(Request $request)
    {
        // 1. 建立代購人的基本查詢：必須是審核通過的
        $query = User::whereHas('agentApplication', function($q) {
            $q->where('status', 'approved');
        });

        // 2. 處理搜尋邏輯 (搜尋暱稱或姓名)
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%");
            });
        }

        // 3. 預載入關聯資料（申請資訊與代購貼文）
        $agents = $query->with(['agentApplication', 'agentPosts' => function($q) {
            $q->where('status', 'open')->orderBy('created_at', 'desc');
        }])->get();

        // 4. 只回傳一個視圖
        return view('shop.store', compact('agents'));
    }

    /**
     * 詳細資料頁面 (保留作為單獨連結使用)
     */
    public function show($id)
    {
        $agent = User::whereHas('agentApplication', function($q) {
            $q->where('status', 'approved');
        })->with(['agentApplication', 'agentPosts' => function($q) {
            $q->where('status', 'open')->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        return view('shop.show', compact('agent'));
    }
}