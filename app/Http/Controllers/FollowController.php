<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class FollowController extends Controller
{
    /**
     * 切換追蹤狀態 (Follow / Unfollow)
     * 透過 AJAX 調用
     */
    public function toggle(Request $request)
    {
        try {
            $followedId = $request->input('followed_id');
            $user = auth()->user();

            // 防止自己追蹤自己
            if ($user->id == $followedId) {
                return response()->json(['error' => '不能追蹤自己'], 400);
            }

            // 進行切換 (如果已追蹤就刪除，未追蹤就新增)
            $status = $user->followings()->toggle($followedId);
            
            // 判斷當前是否為追蹤狀態 (如果出現在 attached 陣列中代表剛新增追蹤)
            $isFollowing = in_array($followedId, $status['attached']);

            return response()->json([
                'status' => 'success',
                'is_following' => $isFollowing
            ]);

        } catch (Exception $e) {
            // 紀錄錯誤到 storage/logs/laravel.log，這對於除錯非常重要
            Log::error('追蹤功能出錯：' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => '伺服器處理要求時發生錯誤'
            ], 500);
        }
    }

    /**
     * 取得目前登入者追蹤的所有代購人列表
     * 用於「追蹤名單」分頁顯示
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // 抓取我追蹤的人，並預載入他們的代購申請與貼文資訊
        // 注意：這裡的關聯名稱需與 User Model 定義一致 (例如 agentApplication)
        $followings = $user->followings()
            ->with(['agentApplication', 'agentPosts'])
            ->latest('follows.created_at')
            ->paginate(12);

        // 修正視圖路徑以對應您之前的檔案結構
        // 如果您的檔案是在 resources/views/dashboard/partials/follows/index.blade.php
        // 則使用 'dashboard.partials.follows.index'
        return view('dashboard.partials.follows.index', compact('followings'));
    }
}