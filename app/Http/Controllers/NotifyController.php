<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AgentApplication; // 記得引入，解決 168 行報錯

class NotifyController extends Controller
{
    /**
     * 專門處理通知頁面
     */
    // app/Http/Controllers/NotifyController.php

public function index(Request $request)
{
    $user = auth()->user();

    // 1. 原有的通知資料
    $notifications = $user->notifications()->latest()->paginate(15);

    // 2. 【新增】抓取這段 HTML 需要的「被報價的請購單」
    // 假設你的邏輯是找出狀態為 'offered' 的請購清單
    $offeredRequests = \App\Models\RequestList::where('user_id', $user->id)
        ->where('status', 'offered')
        ->latest()
        ->get();

    // 3. 解決側邊欄 168 行報錯
    $agentApplication = \App\Models\AgentApplication::where('user_id', $user->id)->first();

    return view('dashboard', [
        'user' => $user,
        'notifications' => $notifications,
        'offeredRequests' => $offeredRequests, // 傳給前端
        'agentApplication' => $agentApplication,
        'section' => 'notifications'
    ]);
}
}