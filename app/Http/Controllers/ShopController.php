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
            // 1. 審核通過的 Agent
            $query = User::whereHas('agentApplication', function($q) {
                $q->where('status', 'approved');
            });

            // 2. 姓名搜尋
            if ($search = $request->input('search')) {
                $query->where('name', 'like', "%{$search}%");
            }

            // ✅ 3. 國家篩選：users.purchasable_countries (JSON)
            if ($country = $request->input('country')) {
                $query->whereJsonContains('purchasable_countries', $country);
            }

            $agents = $query->with([
                'agentApplication', 
                'agentPosts' => fn($q) => $q->where('status', 'open')->orderBy('created_at', 'desc')
            ])->paginate(12)->withQueryString();

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