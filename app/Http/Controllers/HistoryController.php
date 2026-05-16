<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestList;
use App\Models\AgentPost;   
use App\Models\Quote;       
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function agentHistory(Request $request)
    {
        $user = Auth::user();
        $agentHistorySearch = $request->input('agent_history_search');

        // 1. 抓取：代購貼文
        $completedPosts = AgentPost::where('user_id', $user->id)
            ->where('status', 'completed') 
            ->with(['postProducts']) 
            ->when($agentHistorySearch, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%");
            })
            ->latest('updated_at')
            ->get();

        // 2. 抓取：已結案請購清單
       // 2. 抓取：我作為代購，且已經順利結案(completed)的請購清單
        // 2. 抓取：我作為代購（people 欄位記錄著我的 ID），且已經順利結案的請購清單
// 2. 抓取：已接請購清單
$agentHistoryOrders = RequestList::where('people', $user->id)
    // 💡 確保你的第二筆資料在庫裡的 status 確實是 completed。如果 matched 也算完成，可以用 whereIn('status', ['completed', 'matched'])
    ->where('status', 'completed') 
    ->with(['user', 'items']) // 確保預載的是 user，不是 buyer
    ->when($agentHistorySearch, function ($query, $search) {
        return $query->where(function ($q) use ($search) {
            // 修正：因為你的表裡欄位是 title，沒有 list_no
            $q->where('title', 'like', "%{$search}%") 
              ->orWhereHas('user', function ($bQ) use ($search) {
                  $bQ->where('name', 'like', "%{$search}%");
              });
        });
    })
    ->latest('updated_at')
    ->get();

// 💡 3. 修正：計算統計數據（換成你 Migration 裡的真正欄位 budget_total）
$totalIncome = $agentHistoryOrders->sum('budget_total'); 
$finishedOrdersCount = $agentHistoryOrders->count();  // 💡 新增：已完成訂單筆數

        // 4. 抓取報價單狀態
        $quotes_pending = Quote::where('user_id', $user->id)->where('status', 'pending')->latest('updated_at')->get();
        $quotes_accepted = Quote::where('user_id', $user->id)->where('status', 'accepted')->latest('updated_at')->get();
        $quotes_rejected = Quote::where('user_id', $user->id)->where('status', 'rejected')->latest('updated_at')->get();

        $activeTab = $request->input('tab', 'agent-history');

        // 💡 記得在 compact 裡把 'finishedOrdersCount' 補上去
        return view('agent.member', compact(
            'agentHistoryOrders', 
            'completedPosts', 
            'agentHistorySearch', 
            'activeTab',
            'quotes_pending',
            'quotes_accepted',
            'quotes_rejected',
            'totalIncome',
            'finishedOrdersCount' // 👈 補上這行
        ));
    }
}