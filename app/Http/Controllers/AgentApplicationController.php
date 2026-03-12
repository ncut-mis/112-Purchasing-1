<?php

namespace App\Http\Controllers;

use App\Models\AgentApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AgentApplicationController extends Controller
{
    /**
     * 顯示代購人申請表單
     */
    public function create()
    {
        $application = AgentApplication::where('user_id', Auth::id())->first();

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
        $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'id_number' => 'required|string|max:50',
            'id_image_front' => 'required|image|max:2048',
            'id_image_back' => 'required|image|max:2048',
        ], [
            'name.required' => '請填寫真實姓名',
            'country.required' => '請選擇出生地國家',
            'phone.required' => '請填寫聯絡電話',
            'id_number.required' => '請填寫身分證字號',
            'id_image_front.required' => '請上傳身分證正面',
            'id_image_back.required' => '請上傳身分證反面',
        ]);

        $exists = AgentApplication::where('user_id', Auth::id())->exists();
        if ($exists) {
            return redirect()->back()->with('error', '您已經提交過申請，請耐心等候審核。');
        }

        $frontImagePath = $request->file('id_image_front')->store('agent-applications', 'public');
        $backImagePath = $request->file('id_image_back')->store('agent-applications', 'public');

        // 使用逐欄位賦值，避免舊版 fillable 或欄位差異造成必填欄位遺漏
        $application = new AgentApplication();
        $application->user_id = Auth::id();
        $application->name = $request->name;
        $application->country = $request->country;
        $application->phone = $request->phone;
        $application->main_region = $request->country;
        $application->experience = '由身分驗證申請流程建立';
        $application->id_number = $request->id_number;
        $application->id_image_front = $frontImagePath;
        $application->id_image_back = $backImagePath;
        $application->status = 'pending';
        $application->admin_remark = null;

        // 相容舊結構：若資料庫仍有 ID_Card 欄位，則同步寫入
        if (Schema::hasColumn('agent_applications', 'ID_Card')) {
            $application->ID_Card = $request->id_number;
        }

        $application->save();

        return redirect()->route('agent.apply')->with('success', '申請已成功提交！我們將在 3-5 個工作天內完成審核。');
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

    //更新個人檔案
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nickname' => 'required|string|max:255',
            'bio' => 'nullable|string|max:500',
            'countries' => 'nullable|array',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 更新暱稱與簡介 (注意：這會修改 users 資料表的 name 欄位)
        $user->name = $request->nickname;
        $user->bio = $request->bio;
        
        // 處理可代購國家 (轉成 JSON 存入)
        $user->purchasable_countries = json_encode($request->input('countries', []));

        // 處理頭像上傳
        if ($request->hasFile('avatar')) {
            // 如果原本有舊頭像，先刪除
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        return redirect()->route('agent.member')->with('success', '個人資訊已成功更新！');
    }
}