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

    // 【修改】3. 接受報價 (對應路由: quotes.accept)
    public function accept(Quote $quote)
    {
        $requestList = $quote->requestList;

        // 權限檢查：只有案主能選人
        if ($requestList->user_id !== auth()->id()) {
            abort(403, '只有請託單主人能接受報價');
        }

        DB::transaction(function () use ($quote, $requestList) {
            // 接受此報價
            $quote->update(['status' => 'accepted']);
            
            // 自動把該單子的「其他報價」狀態更新為已拒絕 (可選)
            Quote::where('request_list_id', $requestList->id)
                 ->where('id', '!=', $quote->id)
                 ->update(['status' => 'rejected']);
            
            // 寫入代購人 ID，這會讓單子從大廳消失，更新狀態為 matched
            $requestList->update([
                'people' => $quote->user_id,
                'status' => 'matched', 
                'agent_quote_total' => $quote->price // 你的欄位是 $quote->price
            ]);
        });

        // 配合傳統 Form 提交，重定向回上一頁，並帶上 Session 訊息
        return back()->with('success', '已成功選定代購人！');
    }

    // 【新增】4. 拒絕報價 (對應路由: quotes.reject)
    public function reject(Quote $quote)
    {
        $requestList = $quote->requestList;

        if ($requestList->user_id !== auth()->id()) {
            abort(403, '只有請託單主人能拒絕報價');
        }

        DB::transaction(function () use ($quote, $requestList) {
            // 保留被拒絕的報價與明細，只更新狀態
            $quote->update(['status' => 'rejected']);

            // 檢查是否還有「有效中的報價」(pending / accepted / returned)
            $hasActiveQuotes = Quote::where('request_list_id', $requestList->id)
                ->whereIn('status', ['pending', 'accepted', 'returned'])
                ->exists();

            // 若已無有效報價，請託單狀態恢復為等待報價，並清空已配對資料
            if (!$hasActiveQuotes) {
                $requestList->update([
                    'status' => 'pending',
                    'people' => null,
                    'agent_quote_total' => null,
                ]);
            }
        });

        return back()->with('success', '已拒絕該代購人的報價。');
    }
    
        public function return(Quote $quote) {
        // 更新報價單狀態，讓代購人知道需要修改
        $quote->update(['status' => 'returned']); 

        // 也可以同時把請託單狀態改回開放中，視你的業務邏輯而定
        // $quote->requestList->update(['status' => 'pending']);

        return back()->with('success', '報價已退回給代購人修改。');
    }

    // 代購人修改已退回的報價
    public function update(Request $request, Quote $quote)
    {
        if ($quote->user_id !== auth()->id()) {
            abort(403, '只有報價人才能修改報價');
        }

        if ($quote->status !== 'returned') {
            return back()->with('error', '只有已退回的報價才能修改。');
        }

        $requestList = $quote->requestList()->with('items:id,request_list_id')->firstOrFail();
        $deadline = optional($requestList->deadline)->format('Y-m-d');

        $validated = $request->validate([
            'estimated_date'    => array_filter(['required', 'date', $deadline ? 'before_or_equal:' . $deadline : null]),
            'comment'           => 'nullable|string|max:1000',
            'items'             => 'required|array|min:1',
            'items.*.agent_quote' => 'required|numeric|min:0',
        ]);

          $itemIds = $requestList->items->pluck('id')->all();
        $total = 0;

        DB::transaction(function () use ($quote, $validated, $itemIds, &$total) {
            foreach ($validated['items'] as $requestItemId => $itemData) {
                if (!in_array((int) $requestItemId, $itemIds, true)) {
                    continue;
                }

                $unitPrice = (float) $itemData['agent_quote'];
                $total += $unitPrice;

                QuoteItem::updateOrCreate(
                    [
                        'quote_id' => $quote->id,
                        'request_item_id' => (int) $requestItemId,
                    ],
                    ['unit_price' => $unitPrice]
                );
            }

            $quote->update([
                'price'          => $total,
                'estimated_date' => $validated['estimated_date'],
                'comment'        => $validated['comment'] ?? null,
                'status'         => 'pending', // 改回 pending，等請託人再次審核
            ]);
        });

        return back()->with('success', '報價已修改並重新送出，等待請託人確認。');
    }

    // 出貨：將 quote 狀態改為 shipped
    public function ship(Quote $quote)
    {
        // 只有代購人本人能操作
        if ($quote->user_id !== auth()->id()) {
            abort(403, '只有報價人才能標記出貨');
        }

        if ($quote->status !== 'accepted') {
            return back()->with('error', '只有已接單的報價才能標記出貨。');
        }

        DB::transaction(function () use ($quote) {
            $quote->update(['status' => 'shipped']);

            // 同步更新請託單狀態，讓請購人會員專區能立即顯示「商品已出貨」
            $quote->requestList?->update(['status' => 'shipped']);
        });

        return back()->with('success', '已成功標記為已出貨！');
    }

    // 完成：將 quote 狀態改為 completed
    public function complete(Quote $quote)
    {
        // 只有代購人本人能操作
        if ($quote->user_id !== auth()->id()) {
            abort(403, '只有報價人才能標記完成');
        }

        if ($quote->status !== 'shipped') {
            return back()->with('error', '只有已出貨的訂單才能標記為完成。');
        }

        DB::transaction(function () use ($quote) {
            $quote->update(['status' => 'completed']);
            // 同步更新請託單狀態
            $quote->requestList->update(['status' => 'completed']);
        });

        return back()->with('success', '訂單已標記為完成！');
    }
}