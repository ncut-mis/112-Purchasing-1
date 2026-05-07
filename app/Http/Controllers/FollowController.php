<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
            // 1. 驗證請求參數
            // 支援傳入 user_id 或 followed_id，確保前端傳遞彈性
            $followedId = $request->input('user_id') ?? $request->input('followed_id');

            if (!$followedId) {
                return response()->json([
                    'status' => 'error', 
                    'message' => '缺少被追蹤者 ID'
                ], 400);
            }

            $user = Auth::user();

            // 安全檢查：確保使用者已登入
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => '請先登入後再進行操作'
                ], 401);
            }

            // 安全檢查：確保追蹤資料表已建立 (避免 500 錯誤)
            if (!Schema::hasTable('follows')) {
                return response()->json([
                    'status' => 'error',
                    'message' => '系統追蹤功能維護中，請稍後再試'
                ], 503);
            }

            // 2. 防呆：不能追蹤自己
            if ($user->id == $followedId) {
                return response()->json([
                    'status' => 'error',
                    'message' => '您不能追蹤自己'
                ], 400);
            }

            // 3. 執行切換 (調用 User Model 中定義的 toggleFollow)
            // 確保您的 User Model 有對應的 toggleFollow 方法
            $result = $user->toggleFollow($followedId);

            if ($result === false) {
                return response()->json([
                    'status' => 'error', 
                    'message' => '無效的操作目標'
                ], 400);
            }
            
            // 判斷當前是否為追蹤狀態 (attached 陣列長度大於 0 代表剛新增追蹤)
            $isFollowing = count($result['attached']) > 0;

            return response()->json([
                'status' => 'success',
                'is_following' => $isFollowing,
                'message' => $isFollowing ? '已成功追蹤' : '已取消追蹤'
            ]);

        } catch (Exception $e) {
            // 紀錄詳細錯誤至 storage/logs/laravel.log，這能幫助我們在看到紅字時找出病因
            Log::error('追蹤功能執行失敗：' . $e->getMessage());

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

        // 初始化空的分頁物件，防止在極端情況下 (如資料表不存在) 頁面崩潰
        $followings = new LengthAwarePaginator([], 0, 12, request()->query('page', 1), [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        // 只有在資料表存在時才執行正式查詢
        if (Schema::hasTable('follows')) {
            // 抓取我追蹤的人，並預載入申請資訊與「活躍中」的代購貼文
            $followings = $user->followings()
                ->with(['agentApplication', 'agentPosts' => function($q) {
                    $q->where('status', 'open')->latest();
                }])
                ->latest('follows.created_at')
                ->paginate(12);
        }

        // 導向至專屬的追蹤名單局部視圖
        return view('dashboard.partials.follows.index', compact('followings'));
    }
}