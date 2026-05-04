<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use App\Models\Message;
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
        if (!$user) return redirect()->route('login');

        // --- 追蹤名單資料抓取 (Follow Part) ---
        $followings = $user->followings()
            ->with(['agentApplication', 'agentPosts'])
            ->latest('follows.created_at')
            ->get();

        //以下不用動
        $user = Auth::user();
        $currentSection = $request->query('section', 'request-lists');
        $today = now()->toDateString();

        // --- 1. 獲取使用者收藏的 ID 陣列 ---
        $favoriteIds = $user->favorites()
            ->where('favoriteable_type', AgentPost::class)
            ->pluck('favoriteable_id');

        $favoriteAgentPostIds = $favoriteIds->map(fn ($id) => (int) $id)->all();


        // --- 2. 需求列表 (RequestList) 邏輯 ---
        $requestQuery = RequestList::with(['items', 'offers.agent', 'quotes.user'])->where('user_id', $user->id);

        // 過濾狀態與日期（保留既有邏輯，僅排除已結案）
        $requestQuery->where('status', '!=', 'completed');
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


        // --- 4. 我發出的請購 ( status 為 offered ) ---
        $offeredRequests = RequestList::where('user_id', $user->id)
            ->where('status', 'offered')
            ->latest()
            ->get();

        // --- 5. 我承接的報價 (代購人接過的單子) ---
        // 查詢 people = 代購人ID 的清單，即代購人接過的訂單
        $myWorkingOrders = RequestList::with('user') 
            ->where('people', $user->id)
            ->where('status', 'offered')
            ->latest()
            ->get();

        // --- 6. 跟單/訂單 (Orders) 邏輯 ---
        $followOrders = new LengthAwarePaginator([], 0, 9, (int) $request->query('follow_page', 1), [
            'path' => $request->url(),
            'query' => $request->query(),
            'pageName' => 'follow_page',
        ]);

        if (in_array($currentSection, ['follow-orders', 'history-records'], true) && Schema::hasTable('orders')) {
            $followOrdersQuery = Order::with(['seller', 'items', 'source'])
                ->where('buyer_id', $user->id)
                ->where('status', '!=', 'completed');

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

            if ($currentSection === 'follow-orders') {
                $followOrders = $followOrdersQuery->latest()->paginate(9, ['*'], 'follow_page');
            }
        }

        // --- 7. 歷史紀錄 (依請購/跟單分流檢視) ---
        $historyRecords = collect();
        $currentHistoryType = $request->query('history_type', 'request-lists');

        if (!in_array($currentHistoryType, ['request-lists', 'follow-orders'], true)) {
            $currentHistoryType = 'request-lists';
        }

        if ($currentSection === 'history-records') {
            $historySearch = trim((string) $request->query('history_search', ''));

            if ($currentHistoryType === 'request-lists') {
                $completedRequestListsQuery = RequestList::with(['items', 'agent'])
                    ->where('user_id', $user->id)
                    ->where('status', 'completed');

                if ($historySearch !== '') {
                    $completedRequestListsQuery->where(function ($q) use ($historySearch) {
                        $q->where('title', 'like', "%{$historySearch}%")
                            ->orWhere('country', 'like', "%{$historySearch}%")
                            ->orWhere('status', 'like', "%{$historySearch}%")
                            ->orWhereHas('agent', function ($agentQuery) use ($historySearch) {
                                $agentQuery->where('name', 'like', "%{$historySearch}%");
                            });
                    });
                }

                $historyRecords = $completedRequestListsQuery
                    ->latest('updated_at')
                    ->limit(40)
                    ->get()
                    ->map(function (RequestList $requestList) {
                        return [
                            'id' => 'request-' . $requestList->id,
                            'type' => 'request-list',
                            'title' => $requestList->title ?: '未命名請購清單',
                            'status' => $requestList->status,
                            'country' => $requestList->country,
                            'city' => $requestList->city,
                            'agent_name' => optional($requestList->agent)->name,
                            'item_count' => $requestList->items->sum('quantity'),
                            'amount' => (float) ($requestList->agent_quote_total ?? $requestList->budget_total ?? 0),
                            'currency' => $requestList->currency ?: 'TWD',
                            'occurred_at' => $requestList->updated_at,
                            'created_at' => $requestList->created_at,
                            'raw' => $requestList,

                        ];
                    });
            }

            if ($currentHistoryType === 'follow-orders' && Schema::hasTable('orders')) {
                $completedOrdersQuery = Order::with(['seller', 'items', 'source'])
                    ->where('buyer_id', $user->id)
                    ->where('status', 'completed');

                if ($historySearch !== '') {
                    $completedOrdersQuery->where(function ($q) use ($historySearch) {
                        $q->where('order_no', 'like', "%{$historySearch}%")
                            ->orWhere('status', 'like', "%{$historySearch}%")
                            ->orWhereHas('seller', function ($sellerQuery) use ($historySearch) {
                                $sellerQuery->where('name', 'like', "%{$historySearch}%");
                            })
                            ->orWhereHasMorph('source', [AgentPost::class, RequestList::class], function ($sourceQuery) use ($historySearch) {
                                $sourceQuery->where('title', 'like', "%{$historySearch}%");
                            });
                    });
                }

                $historyRecords = $completedOrdersQuery
                    ->latest('updated_at')
                    ->limit(40)
                    ->get()
                    ->map(function (Order $order) {
                        $historyTitle = $order->source?->title
                            ?? data_get($order->recipient_data, 'post_title')
                            ?? ('訂單 ' . $order->order_no);

                        return [
                            'id' => 'order-' . $order->id,
                            'type' => 'follow-order',
                            'title' => $historyTitle,
                            'status' => $order->status,
                            'country' => null,
                            'city' => null,
                            'agent_name' => optional($order->seller)->name,
                            'item_count' => $order->items->sum('quantity'),
                            'amount' => (float) $order->total_amount,
                            'currency' => $order->currency ?: 'TWD',
                            'occurred_at' => $order->updated_at,
                            'created_at' => $order->created_at,
                            'raw' => $order,
                        ];
                    });
            }
        }


        // --- 8. 統計數據 (關鍵修正區塊) ---
        $stats = [
            'ongoing_requests' => RequestList::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'offered', 'matched'])
                ->count(),
            
            // 修正點：徹底移除 request_list_id 的過濾與 exists 子查詢
            // 系統報錯是因為 messages 表沒有 request_list_id 欄位
            'unread_messages' => Message::where('receiver_id', $user->id)
                ->whereNull('read_at')
                ->count(),

            'favorite_posts' => count($favoriteAgentPostIds),
            'reviews_score' => '4.9 / 5',
        ];
        // --- 9. 新增：獲取報價列表 (供通知中心使用) ---
                // 抓出屬於使用者的請購單所收到的所有報價，並預載入相關資料
                 $offers = \App\Models\Quote::whereHas('requestList', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->with(['user', 'requestList.items']) 
                ->latest()
                ->get();
                
        return view('dashboard', compact(
            'requestLists',
            'favoriteAgentPosts',
            'favoriteAgentPostIds',
            'followOrders',
            'currentSection',
            'stats',
            'offeredRequests',
            'myWorkingOrders',
            'historyRecords',
            'currentHistoryType',
            'offers', 
            'followings'
        ));
    }
}