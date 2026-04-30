<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AgentApplication; // 記得引入，解決 168 行報錯
use App\Models\QuoteController; 

class NotifyController extends Controller
{
    /**
     * 專門處理通知頁面
     */
    // app/Http/Controllers/NotifyController.php

public function index()
{
    $user = auth()->user();

    // 抓出「屬於這個使用者的需求單」收到的「所有報價」
    $offers = \App\Models\RequestOffer::whereHas('requestList', function($query) use ($user) {
        $query->where('user_id', $user->id);
    })
    ->with(['user', 'requestList']) // 效能優化
    ->latest()
    ->get();

    return view('notifications.index', compact('offers'));
}
}