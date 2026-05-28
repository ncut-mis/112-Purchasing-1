<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logistics; // 確保你有對應的 Model
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogisticsController extends Controller
{
    public function index()
    {
        // 抓取所有資料
        $logistics = Logistics::where('user_id', Auth::id())->get();
        return view('dashboard.partials.settings.logistics', compact('logistics'));
    }

    public function save(Request $request)
    {
        // 1. 驗證輸入（建議加入，確保資料完整）
        $request->validate([
            'name' => 'required|string|max:255',
            // 其他驗證規則...
        ]);

        // 2. 透過 where('user_id', Auth::id()) 確保使用者只能操作自己的資料
        // 如果是編輯，找不到該 ID 的物流會直接拋出 404，保護資料不被非法竄改
        $data = $request->id 
                ? Logistics::where('user_id', Auth::id())->findOrFail($request->id) 
                : new Logistics(['user_id' => Auth::id()]); // 新增時自動寫入 user_id

        // 3. 填入資料 (利用模型 fillable)
        $data->name = $request->name;
        $data->status = $request->status;
        $data->ship_type = $request->ship_type;
        $data->payment_method = $request->payment;
        $data->temp_layer = $request->temp_layer;
        
        // 🎯 由於 Model 設定了 casts => array，直接傳入陣列即可，Laravel 會自動轉 JSON
        $data->available_times = $request->times; 

        // 4. 儲存
        $data->save();

        return back()->with('success', '設定已儲存');
    }
    public function bySeller($sellerId)
{
    $logistics = DB::table('logistics')
        ->where('status', 1)
        ->where('user_id', $sellerId)
        ->get();

    return response()->json($logistics);
}
}