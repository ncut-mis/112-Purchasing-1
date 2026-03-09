<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AgentApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AgentApplicationController extends Controller
{
    /**
     * 顯示申請頁面
     */
    public function create()
    {
        // 如果已經申請過，直接導向狀態頁
        if (Auth::user()->agentApplication) {
            return redirect()->route('agent.status');
        }

        return view('agent.apply');
    }

    /**
     * 儲存申請資料
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string',
            'phone' => 'required|string',
            'id_number' => 'required|string',
            'id_image_front' => 'required|image|max:2048',
            'id_image_back' => 'required|image|max:2048',
        ]);

        // 處理圖片上傳
        $frontPath = $request->file('id_image_front')->store('agent-applications', 'public');
        $backPath = $request->file('id_image_back')->store('agent-applications', 'public');

        // 建立申請紀錄 (包含之前遺漏的 main_region 和 experience)
        AgentApplication::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'country' => $request->country,
            'phone' => $request->phone,
            'main_region' => $request->country, // 預設為居住國家
            'experience' => '由身分驗證申請流程建立',
            'id_number' => $request->id_number,
            'id_image_front' => $frontPath,
            'id_image_back' => $backPath,
            'status' => 'pending',
        ]);

        return redirect()->route('agent.status')->with('success', '申請已送出，請靜候審核。');
    }

    /**
     * 顯示申請進度 (解決目前報錯的關鍵方法)
     */
    public function status()
    {
        $application = Auth::user()->agentApplication;

        if (!$application) {
            return redirect()->route('agent.apply');
        }

        return view('agent.status', compact('application'));
    }

    /**
     * 代購人更新個人檔案 (會員專區)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nickname' => 'required|string|max:255',
            'bio' => 'nullable|string|max:500',
            'countries' => 'nullable|array',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 更新基本欄位
        $user->name = $request->nickname;
        $user->bio = $request->bio;
        
        // 將國家陣列轉為 JSON 儲存
        $user->purchasable_countries = json_encode($request->input('countries', []));

        // 處理頭像上傳
        if ($request->hasFile('avatar')) {
            // 刪除舊頭像 (如果有)
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return redirect()->route('agent.member')->with('success', '個人資訊已更新！');
    }
}