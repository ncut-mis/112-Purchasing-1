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

        $totalOpenPosts = max(AgentPost::where('status', 'open')->count(), 1);

        $hotPosts = AgentPost::with(['user', 'products'])
            ->withCount(['favorites', 'orders'])
            ->where('status', 'open')
            ->get()
            ->map(function (AgentPost $post) use ($totalOpenPosts) {
                $favoriteRatio = min(($post->favorites_count / $totalOpenPosts) * 100, 100);
                $orderRatio = min(($post->orders_count / $totalOpenPosts) * 100, 100);

                $score = (int) round(($favoriteRatio * 0.55) + ($orderRatio * 0.45));
                $post->hot_score = max(0, min(100, $score));

                return $post;
            })
            ->sortByDesc('hot_score')
            ->take(6)
            ->values();

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
             ->where('status', 'open')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
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

        $hotPosts = AgentPost::with(['user', 'products'])
            ->withCount(['favorites', 'orders'])
            ->where('status', 'open')
            ->get()
            ->map(function (AgentPost $post) use ($totalOpenPosts) {
                $favoriteRatio = min(($post->favorites_count / $totalOpenPosts) * 100, 100);
                $orderRatio = min(($post->orders_count / $totalOpenPosts) * 100, 100);

                $score = (int) round(($favoriteRatio * 0.55) + ($orderRatio * 0.45));
                $post->hot_score = max(0, min(100, $score));

                return $post;
            })
            ->sortByDesc('hot_score')
            ->take(6)
            ->values();
        
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