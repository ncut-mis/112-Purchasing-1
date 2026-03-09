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

        AgentApplication::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'country' => $request->country,
            'phone' => $request->phone,
            // 舊版資料表仍要求 main_region / experience，這裡同步寫入以相容
            'main_region' => $request->country,
            'experience' => '由身分驗證申請流程建立',
            'id_number' => $request->id_number,
            'id_image_front' => $frontImagePath,
            'id_image_back' => $backImagePath,
            'status' => 'pending',
            'admin_remark' => null,
        ]);

        return redirect()->route('agent.apply')->with('success', '申請已成功提交！我們將在 3-5 個工作天內完成審核。');
    }
}