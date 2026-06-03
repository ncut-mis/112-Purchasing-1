<?php

namespace App\Http\Controllers;

use App\Models\RequestList;
use App\Models\Favorite;
use App\Models\Logistics;
use App\Models\AgentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AgentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = RequestList::with(['items', 'user', 'offers'])
            ->whereIn('status', ['pending', 'offered'])
            ->whereDate('deadline', '>=', now()->toDateString())
            ->whereNull('people')
            ->latest();

        // 關鍵字搜尋
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

        // 國家篩選
        $country = $request->query('country');

        if ($country && $country !== 'all') {
            $query->where('country', $country);
        }

        // 時間篩選
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

        // ===== 小鈴鐺勾選篩選 =====
        $selectedBuyerIds = AgentNotification::where('agent_id', auth()->id())
            ->where('is_selected', true)
            ->pluck('buyer_id')
            ->unique()
            ->toArray();

        if (!empty($selectedBuyerIds)) {
            $query->whereIn('user_id', $selectedBuyerIds);
        }

        $requests = $query->paginate(12)->withQueryString();

        $favoritedRequestListIds = Auth::check()
            ? Favorite::where('user_id', Auth::id())
                ->where('favoriteable_type', RequestList::class)
                ->pluck('favoriteable_id')
                ->map(fn ($id) => (int) $id)
                ->all()
            : [];

        $hasActiveLogistics = Auth::check()
            ? Logistics::where('user_id', Auth::id())
                ->where('status', true)
                ->exists()
            : false;

        return view('agent.dashboard', [
            'requests' => $requests,
            'requestLists' => $requests,
            'favoritedRequestListIds' => $favoritedRequestListIds,
            'selectedCountry' => $country ?? 'all',
            'selectedTime' => $selectedTime,
            'keyword' => $keyword,
            'hasActiveLogistics' => $hasActiveLogistics,
        ]);
    }

  public function clearFilter()
{
    // 1. 記錄執行狀態 (檢查 storage/logs/laravel.log)
    \Log::info('清除按鈕已觸發，使用者 ID: ' . auth()->id());

    // 2. 清除資料庫
    \App\Models\AgentNotification::where('agent_id', auth()->id())
        ->update(['is_selected' => false]);

    // 3. 強制重導向至 dashboard
    return redirect()->route('agent.dashboard');
}
}