<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use App\Models\RequestList;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // 撈取最新的 6 筆代購貼文 (必須是開放狀態)
        $posts = AgentPost::with('user') 
            ->where('status', 'open')
            ->latest()
            ->take(6)
            ->get();

        // 撈取最新的 8 筆請購清單
        $requests = RequestList::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(8)
            ->get();

        $favoritedAgentPostIds = auth()->check()
            ? auth()->user()->favorites()
                ->where('favoriteable_type', AgentPost::class)
                ->pluck('favoriteable_id')
                ->map(fn ($id) => (int) $id)
                ->all()
            : [];

        return view('home', compact('posts', 'requests', 'favoritedAgentPostIds'));
    }

    /**
     * 搜尋代購貼文 (首頁搜尋表單使用)
     */
        public function search(Request $request)
    {
        $query = AgentPost::withCount('products')
            ->with('user')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('products', function ($productQuery) use ($search) {
                // ✅ 只搜商品名稱
                $productQuery->where('name', 'LIKE', "%{$search}%");
            });
        }
        if ($request->filled('country')) {
        $query->where('country', $request->country);
        }

        $posts = $query->paginate(12)->withQueryString();
        
        $requests = RequestList::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(8)
            ->get();

        return view('home', [
            'agentPosts' => $posts,
            'requests' => $requests,
            'countries' => ['日本', '韓國', '美國', '歐洲', '澳洲', '其他'],  // ✅ 國家選項
            'selectedCountry' => $request->country ?? '',  // ✅ 保留選中
            'searchQuery' => $request->search ?? ''
        ]);

    }


}