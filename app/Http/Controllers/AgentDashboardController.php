<?php

namespace App\Http\Controllers;

use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Favorite;

class AgentDashboardController extends Controller
{
    /**
     * 顯示代購人接單大廳 (修正篩選欄位錯誤)
     */
    public function index(Request $request)
    {
        // 1. 初始化查詢
        $query = RequestList::with(['items', 'user'])
            ->where('status', 'pending')
            ->whereDate('deadline', '>=', now()->toDateString())
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

        // 3. 修正點：將 'location' 改為 'country'
        // 根據錯誤訊息，您的資料表欄位名稱應該是 country
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

        // 6. 回傳視圖
        return view('agent.dashboard', [
            'requests' => $requests,
            'requestLists' => $requests, // 保持相容性
            'favoritedRequestListIds' => $favoritedRequestListIds,
            'selectedCountry' => $country ?? 'all',
            'selectedTime' => $selectedTime,
            'keyword' => $keyword,
        ]);
    }
}