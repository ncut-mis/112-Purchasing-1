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
<<<<<<< HEAD
        {
            $quote = Quote::with(['requestList', 'user', 'quoteItems'])->findOrFail($id);
            $requestList = $quote->requestList;

            if ((int) $requestList->user_id !== (int) auth()->id()) {
                abort(403, '您無權接受此報價');
            }

            DB::transaction(function () use ($quote, $requestList) {
                Quote::where('request_list_id', $requestList->id)
                    ->where('id', '!=', $quote->id)
                    ->update(['status' => 'rejected']);

                $quote->update(['status' => 'accepted']);

                $requestList->update([
                    'status'       => 'matched',
                    'people'       => $quote->user_id,
                    'budget_total' => $quote->price,
                ]);
            });

            $cart = session()->get('cart', []);
            $cart[$quote->id] = [
                "id"         => $quote->id,
                "name"       => "請託單報價: " . $requestList->title,
                "price"      => $quote->price,
                "agent_name" => $quote->user->name,
                "quantity"   => 1,
                "items"      => $quote->quoteItems->pluck('name')->toArray(),
            ];
            session()->put('cart', $cart);

            return redirect()->route('shopping.cart')->with('success', '已接受報價，訂單已轉入購物車！');
        }


    public function reject(Quote $quote)
    {
        $requestList = $quote->requestList;

        if ((int) $requestList->user_id !== (int) auth()->id()) {
            abort(403, '您無權拒絕此報價');
        }

        if ($quote->status === 'accepted') {
            return back()->with('error', '此報價已被接受，無法拒絕。');
        }

        $quote->update(['status' => 'rejected']);

        $hasPendingOrAccepted = Quote::where('request_list_id', $requestList->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();

        if (! $hasPendingOrAccepted) {
            $requestList->update(['status' => 'pending']);
        }

        return back()->with('success', '已拒絕該筆報價。');
    }

=======
{
    // 1. 取得這筆被接受的報價
    $quote = Quote::with(['requestList', 'user', 'quoteItems'])->findOrFail($id);
    
    // 【關鍵：定義變數】
    $requestListId = $quote->request_list_id;

    // 2. 更新請託單狀態
    $quote->requestList->update([
        'status'       => 'matched', 
        'people'       => $quote->user_id, 
        'budget_total' => $quote->price,  
    ]);

    // 3. 更新這筆報價單狀態
    $quote->update(['status' => 'accepted']);

    // 4. 退回其他人的報價 (現在這裏就不會報錯了)
    Quote::where('request_list_id', $requestListId)
        ->where('id', '!=', $id)
        ->where('status', 'pending')
        ->update(['status' => 'rejected']);

    // 5. 寫入 Session
    $cart = session()->get('cart', []);
    $cart[$quote->id] = [
        "id"         => $quote->id,
        "request_id" => $requestListId,
        "name"       => "請託單報價: " . $quote->requestList->title,
        "price"      => $quote->price,
        "agent_name" => $quote->user->name,
        "quantity"   => 1,
        "items"      => $quote->quoteItems->pluck('name')->toArray(),
    ];
    session()->put('cart', $cart);

    return redirect()->route('shopping.cart')->with('success', '接受報價成功！');
}
>>>>>>> 7924682 (導入結帳功能)
}