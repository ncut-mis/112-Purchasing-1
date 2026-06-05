<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use App\Models\RequestList;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // 撈取最新的 6 筆代購團
        $agentPosts = AgentPost::with(['user', 'products'])
            ->publicVisible()
            ->latest()
            ->take(6)
            ->get();

        // 撈取最新的 8 筆請託單

        AgentPost::recalculateHotScores();

        $hotPosts = AgentPost::with(['user', 'products'])
            ->publicVisible()
            ->orderByDesc('hot_score')
            ->take(6)
            ->get();

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

        return view('home', compact('agentPosts', 'hotPosts', 'requests', 'favoritedAgentPostIds'));
    }

    /**
     * 搜尋代購團 (首頁搜尋表單使用)
     */
    public function search(Request $request)
    {
        $query = AgentPost::withCount('products')
            ->with('user')
             ->publicVisible()
            ->latest();

         // 若有指定貼文 ID，優先精準篩選，避免同名/相似標題造成多筆誤中
        if ($request->filled('post_id')) {
            $query->where('id', (int) $request->post_id);
        } elseif ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                // ✅ 新增：標題搜尋 OR 商品搜尋
                $q->whereHas('products', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhere('title', 'LIKE', "%{$search}%");  // ✅ 標題搜尋
            });
        }

        // 國家篩選（AND）
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        $posts = $query->paginate(12)->withQueryString();

            $favoritedAgentPostIds = auth()->check()
            ? auth()->user()->favorites()
                ->where('favoriteable_type', AgentPost::class)
                ->pluck('favoriteable_id')
                ->map(fn ($id) => (int) $id)
                ->all()
            : [];

            $totalOpenPosts = max(AgentPost::where('status', 'open')->count(), 1);

        AgentPost::recalculateHotScores();

        $hotPosts = AgentPost::with(['user', 'products'])
            ->publicVisible()
            ->orderByDesc('hot_score')
            ->take(6)
            ->get();
        
        $requests = RequestList::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(8)
            ->get();

        return view('home', [
            'agentPosts' => $posts,
            'requests' => $requests,
            'favoritedAgentPostIds' => $favoritedAgentPostIds,
            'countries' => ['日本', '韓國', '美國', '歐洲', '澳洲', '其他'],
            'selectedCountry' => $request->country ?? '',
            'searchQuery' => $request->search ?? '',
            'hotPosts' => $hotPosts
        ]);
    }



}