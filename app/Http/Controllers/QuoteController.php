<?php
namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuoteController extends Controller
{
    // 1. 報價儲存（保持不變，適合前端使用 AJAX 呼叫）
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_list_id' => 'required|exists:request_lists,id',
            'agent_quote_total' => 'required|numeric|min:0.01',
            'estimated_date' => 'required|date',
            'comment' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:request_items,id',
            'items.*.agent_quote' => 'required|numeric|min:0'
        ]);

        $requestList = RequestList::findOrFail($validated['request_list_id']);

        // 防呆：禁止重複報價
        $exists = Quote::where('request_list_id', $requestList->id)
                       ->where('user_id', auth()->id())
                       ->exists();
        if ($exists) {
            return response()->json(['message' => '您已經報過價了！'], 422);
        }

        // 建立報價單
        $quote = Quote::create([
            'request_list_id' => $requestList->id,
            'user_id'         => auth()->id(),
            'price'           => $validated['agent_quote_total'],
            'estimated_date'  => $validated['estimated_date'],
            'comment'         => $validated['comment'] ?? null,
            'status'          => 'pending',
        ]);

        // 儲存每個商品的單價
        if (Schema::hasTable('quote_items')) {
            foreach ($validated['items'] as $item) {
                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'request_item_id' => $item['id'],
                    'unit_price' => $item['agent_quote'],
                ]);
            }
        }

        // 更新需求單狀態為「已有報價」
        $requestList->update(['status' => 'offered']);

        return response()->json(['status' => 'success', 'message' => '報價成功，請待案主選定！']);
    }

    // 【新增】2. 查看報價詳細內容 (對應路由: quotes.show)
   public function show(Quote $quote)
{
    $requestList = $quote->requestList;

    if ($requestList->user_id !== auth()->id() && $quote->user_id !== auth()->id()) {
        abort(403, '您無權查看此報價');
    }

    // 優化：預載入 quoteItems，這樣畫面才拿得到單項單價
    $quote->load(['user', 'requestList.items', 'quoteItems']);

    return view('quotes.show', compact('quote'));
}
        public function accept($id)
        {
            // 1. 取得該筆報價，並關聯請託單 (requestList) 與報價人 (user)
            $quote = Quote::with(['requestList', 'user', 'quoteItems'])->findOrFail($id);

            // 2. 更新請託單 (RequestList) 的關鍵欄位
            // 這裡直接執行你要求的動作：連結 ID 與 金額
            $quote->requestList->update([
                'status'       => 'matched',     // 狀態改為已接受
                'people'       => $quote->user_id, // 報價人 ID 轉入 people 欄位
                'budget_total' => $quote->price,  // 報價金額連結到 budget_total 欄位
            ]);

            
           

            // 4. 將資料推送到購物車 Session (確保購物車能顯示正確金額與代購人)
            $cart = session()->get('cart', []);
            $cart[$quote->id] = [
                "id"         => $quote->id,
                "name"       => "請託單報價: " . $quote->requestList->title,
                "price"      => $quote->price,        // 最終報價金額
                "agent_name" => $quote->user->name,   // 代購人名稱
                "quantity"   => 1,
                "items"      => $quote->quoteItems->pluck('name')->toArray(),
            ];
            session()->put('cart', $cart);

            // 5. 導向購物車頁面
            return redirect()->route('shopping.cart')->with('success', '已接受報價，訂單已轉入購物車！');
        }
}