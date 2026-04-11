<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logistics; // 確保你有對應的 Model

class LogisticsController extends Controller
{
    public function index()
    {
        // 抓取所有資料
        $logistics = Logistics::all();
        
        // 關鍵點：路徑改為 dashboard.settings.logistics
        return view('dashboard.partials.settings.logistics', compact('logistics'));
    }

    public function save(Request $request)
    {
        // 如果有傳入 id 就更新，沒有就新增
        $data = $request->id ? Logistics::findOrFail($request->id) : new Logistics;

        $data->name = $request->name;
        $data->status = $request->status;
        $data->ship_type = $request->ship_type;
        $data->payment_method = $request->payment;
        // 將時段陣列存成 JSON
        $data->available_times = $request->times ? json_encode($request->times) : null;
        

            // --- 補上這兩行 ---
        // 儲存溫層資料 (對應 Blade 中的 select name="temp_layer")
        $data->temp_layer = $request->temp_layer; 
        // ----------------
        // 將時段陣列存成 JSON
        $data->available_times = $request->times ? json_encode($request->times, JSON_UNESCAPED_UNICODE) : null;

        $data->save();

        return back()->with('success', '設定已儲存');
    }
}