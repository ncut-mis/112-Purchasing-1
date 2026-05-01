<?php
namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\RequestList;
use Illuminate\Http\Request; // 必須補上這行
use Illuminate\Support\Facades\DB; // 必須補上這行
use Illuminate\Support\Facades\Schema;

class QuoteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_list_id' => 'required|exists:request_lists,id',
            'agent_quote_total' => 'required|numeric|min:0.01',
            'time' => 'required|string|max:500',
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

        // 建立報價單 (不要更新 RequestList 的 people)
        $quote = Quote::create([
            'request_list_id' => $requestList->id,
            'user_id'         => auth()->id(),
            'price'           => $validated['agent_quote_total'],
            'comment'         => $validated['time'],
            'status'          => 'pending',
        ]);

        // 儲存每個商品的單價（如果表存在）
        if (\Illuminate\Support\Facades\Schema::hasTable('quote_items')) {
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

    public function accept(Quote $quote)
    {
        $requestList = $quote->requestList;

        // 權限檢查：只有案主能選人
        if ($requestList->user_id !== auth()->id()) {
            abort(403);
        }

        DB::transaction(function () use ($quote, $requestList) {
            $quote->update(['status' => 'accepted']);
            
            // 寫入代購人 ID，這會讓單子從大廳消失
            $requestList->update([
                'people' => $quote->user_id,
                'status' => 'matched', 
                'agent_quote_total' => $quote->price
            ]);
        });

        return response()->json(['message' => '已選定代購人！']);
    }
}