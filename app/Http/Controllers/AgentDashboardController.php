<?php

namespace App\Http\Controllers;

use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Favorite;
use App\Models\Logistics;

class AgentDashboardController extends Controller
{
    /**
     * 顯示代購人接單大廳 (已整合小鈴鐺點擊特定買家篩選功能)
     */
    public function index(Request $request)
    {
        // 1. 初始化查詢
        $query = RequestList::with(['items', 'user', 'offers'])
        // 狀態：顯示等待中 (pending) 與 有人報價但尚未成交 (offered) 的單子
        ->whereIn('status', ['pending', 'offered']) 
        // 時間：截止日期必須大於或等於今天 (未過期)
        ->whereDate('deadline', '>=', now()->toDateString())
        // 排除已成交的單子 (people 欄位為空表示還沒人接單)
        ->whereNull('people')
        ->latest();

        // 2. 處理關鍵字搜尋
        $keyword = trim((string) $request->query('q', ''));
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('note', 'like', "%{$keyword}%")
                    ->orWhereHas('items', function ($itemQuery) use ($keyword) {
                        $itemQuery->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        // 🎯 【新增核心邏輯】：處理從小鈴鐺「前往查看」帶過來的買家篩選
        // 只要網址後方有 ?search_buyer_id=X ，大廳就只會顯示該買家的清單
        $searchBuyerId = $request->query('search_buyer_id');
        if ($searchBuyerId) {
            $query->where('user_id', $searchBuyerId);
        }

        // 3. 修正點：將 'location' 改為 'country'
        $country = $request->query('country');
        if ($country && $country !== 'all') {
            $query->where('country', $country);
        }

        // 4. 處理時間篩選
        $selectedTime = $request->query('time', 'all');
        $today = Carbon::today();

        if ($selectedTime === 'urgent') {
            $query->whereDate('deadline', '>=', $today)
                ->whereDate('deadline', '<=', Carbon::now()->addDay());
        } elseif ($selectedTime === 'three_days') {
            $query->whereDate('deadline', '>=', $today)
                ->whereDate('deadline', '<=', Carbon::now()->addDays(3));
        } elseif ($selectedTime === 'this_week') {
            $query->whereDate('deadline', '>=', $today)
                ->whereDate('deadline', '<=', Carbon::now()->endOfWeek(Carbon::SUNDAY));
        }

        // 5. 執行查詢與分頁
        $requests = $query->paginate(12)->withQueryString();

        $favoritedRequestListIds = Auth::check()
            ? Favorite::query()
                ->where('user_id', Auth::id())
                ->where('favoriteable_type', RequestList::class)
                ->pluck('favoriteable_id')
                ->map(fn ($id) => (int) $id)
                ->all()
            : [];

        $hasActiveLogistics = Auth::check()
            ? Logistics::where('user_id', Auth::id())->where('status', true)->exists()
            : false;

        // 6. 回傳視圖
        return view('agent.dashboard', [
            'requests' => $requests,
            'requestLists' => $requests, // 保持相容性
            'favoritedRequestListIds' => $favoritedRequestListIds,
            'selectedCountry' => $country ?? 'all',
            'selectedTime' => $selectedTime,
            'keyword' => $keyword,
            'searchBuyerId' => $searchBuyerId, // 💡 傳給前端，未來如果想做「清除篩選」按鈕可以用
            'hasActiveLogistics' => $hasActiveLogistics,
        ]);
    }
}