<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use App\Models\Order;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $currentSection = $request->query('section', 'request-lists');
        $today = now()->toDateString();

        // --- 1. 獲取使用者收藏的 ID 陣列 ---
        // 先抓出 ID，這會用於後續的 Query 過濾以及統計數量
        $favoriteIds = $user->favorites()
            ->where('favoriteable_type', AgentPost::class)
            ->pluck('favoriteable_id');

        $favoriteAgentPostIds = $favoriteIds->map(fn ($id) => (int) $id)->all();


        // --- 2. 需求列表 (RequestList) 邏輯 ---
        $requestQuery = RequestList::with(['items', 'offers.agent'])->where('user_id', $user->id);

        // 過濾狀態與日期
        $requestQuery->where(function ($q) use ($today) {
            $q->where('status', '!=', 'pending')
                ->orWhereDate('deadline', '>=', $today);
        });

        // 需求列表搜尋
        if ($requestSearch = $request->get('request_search')) {
            $requestQuery->where(function ($q) use ($requestSearch) {
                $q->where('title', 'like', "%{$requestSearch}%")
                  ->orWhere('note', 'like', "%{$requestSearch}%")
                  ->orWhere('status', 'like', "%{$requestSearch}%");
            });
        }
        $requestLists = $requestQuery->latest()->paginate(10, ['*'], 'request_page');


        // --- 3. 收藏貼文 (AgentPost) 邏輯 ---
        // 關鍵修正：只在這邊定義一次 Query，並帶入收藏 ID 過濾
        $favoriteAgentPostsQuery = AgentPost::with(['user', 'products'])
            ->whereIn('id', $favoriteAgentPostIds);

        // 收藏搜尋
        if ($favoriteSearch = trim((string) $request->query('favorite_search', ''))) {
            $favoriteAgentPostsQuery->where(function ($q) use ($favoriteSearch) {
                $q->where('title', 'like', "%{$favoriteSearch}%")
                    ->orWhere('description', 'like', "%{$favoriteSearch}%")
                    ->orWhere('country', 'like', "%{$favoriteSearch}%")
                    ->orWhereHas('user', function ($userQuery) use ($favoriteSearch) {
                        $userQuery->where('name', 'like', "%{$favoriteSearch}%");
                    });
            });
        }
        // 執行分頁
        $favoriteAgentPosts = $favoriteAgentPostsQuery->latest()->paginate(9, ['*'], 'favorite_page');


            // ---4. // 加上預加載，顯示名字才快
             $offeredRequests = RequestList::with('agent') 
                ->where('user_id', $user->id)
                ->where('status', 'offered')
                ->latest() 
                ->get();

            // --- 5. 我承接的報價 (我當代購，去幫別人買) ---
            // 這裡用一個新變數名稱，例如 $myWorkingOrders
            $myWorkingOrders = RequestList::with('user') // 加上預加載發案人的資訊
                ->where('people', $user->id) // 篩選代購人是我自己
                ->where('status', 'offered')
                ->latest()
                ->get();

        // --- 5. 跟單/訂單 (Orders) 邏輯 ---
        $followOrders = new LengthAwarePaginator([], 0, 9, (int) $request->query('follow_page', 1), [
            'path' => $request->url(),
            'query' => $request->query(),
            'pageName' => 'follow_page',
        ]);

        if ($currentSection === 'follow-orders' && Schema::hasTable('orders')) {
            $followOrdersQuery = Order::with(['seller', 'items', 'source'])
                ->where('buyer_id', $user->id);

            if ($followSearch = trim((string) $request->query('follow_search', ''))) {
                $followOrdersQuery->where(function ($q) use ($followSearch) {
                    $q->where('order_no', 'like', "%{$followSearch}%")
                        ->orWhere('status', 'like', "%{$followSearch}%")
                        ->orWhere('tracking_number', 'like', "%{$followSearch}%")
                        ->orWhereHasMorph('source', [AgentPost::class, RequestList::class], function ($sourceQuery) use ($followSearch) {
                            $sourceQuery->where('title', 'like', "%{$followSearch}%");
                        })
                        ->orWhereHas('seller', function ($sellerQuery) use ($followSearch) {
                            $sellerQuery->where('name', 'like', "%{$followSearch}%");
                        });
                });
            }
            $followOrders = $followOrdersQuery->latest()->paginate(9, ['*'], 'follow_page');
        }


        // --- 6. 統計數據 ---
        $stats = [
            'ongoing_requests' => RequestList::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'offered', 'matched'])
                ->count(),
            'unread_messages' => 0,
            'favorite_posts' => count($favoriteAgentPostIds), // 這裡顯示的是過濾後的收藏總數
            'reviews_score' => '4.9 / 5',
        ];

        return view('dashboard', compact(
            'requestLists',
            'favoriteAgentPosts',
            'favoriteAgentPostIds',
            'followOrders',
            'currentSection',
            'stats',
            'offeredRequests',
            'myWorkingOrders'
        ));
    }
}