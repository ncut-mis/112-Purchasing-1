<?php

namespace App\Http\Controllers;

use App\Models\AgentApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentApplicationController extends Controller
{
    /**
     * 顯示代購人申請表單
     */
    public function create()
    {
        // 檢查使用者是否已經提交過申請
        $application = AgentApplication::where('user_id', Auth::id())->first();

        // 如果已經申請過，導向狀態頁面或是顯示已申請訊息
        if ($application) {
            return view('agent.status', compact('application'));
        }

        return view('agent.apply');
    }

    /**
     * 儲存代購人申請資料
     */
    public function store(Request $request)
    {
        // 1. 驗證輸入欄位
        $request->validate([
            'phone' => 'required|string|max:20',
            'main_region' => 'required|string|max:100',
            'experience' => 'required|string|min:10',
        ], [
            'phone.required' => '請填寫聯絡電話',
            'main_region.required' => '請填寫主要代購地區（如：日本、韓國）',
            'experience.required' => '請簡單分享您的代購經驗',
            'experience.min' => '代購經驗說明請至少輸入 10 個字',
        ]);

        // 2. 再次檢查防止重複提交（保險起見）
        $exists = AgentApplication::where('user_id', Auth::id())->exists();
        if ($exists) {
            return redirect()->back()->with('error', '您已經提交過申請，請耐心等候審核。');
        }

        // 3. 建立申請紀錄
        AgentApplication::create([
            'user_id' => Auth::id(),
            'phone' => $request->phone,
            'main_region' => $request->main_region,
            'experience' => $request->experience,
            'status' => 'pending', // 預設為待審核
        ]);

        // 4. 導向回申請頁面並顯示成功訊息
        return redirect()->route('agent.apply')->with('success', '申請已成功提交！我們將在 3-5 個工作天內完成審核。');
    }

    public function updateProfile(Request $request)
    {
    $user = Auth::user();

    // 驗證輸入
    $request->validate([
        'nickname' => 'required|string|max:255',
        'bio' => 'nullable|string|max:500',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    // 更新暱稱與簡介 (假設你之後會加欄位到 users 表)
    // 目前我們先用 name 代替暱稱
    $user->name = $request->nickname;
    $user->bio = $request->bio; 

    // 處理頭像上傳 (如果有上傳新圖片)
    if ($request->hasFile('avatar')) {
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path; // 記得之後在資料庫補 avatar 欄位
    }

    $user->save();

    return redirect()->route('agent.member')->with('success', '個人資訊已成功更新！');
    }
}