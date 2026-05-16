<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\RequestList;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\PostProduct;
use App\Models\Logistics;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $followOrders = Order::where('buyer_id', $userId)
                            ->where('status', 'pending_payment')
                            ->with(['source', 'seller']) 
                            ->get();

        $requestLists = RequestList::where('user_id', $userId)
                            ->where('status', 'matched')
                            ->whereNotNull('people') 
                            ->with('agent')          
                            ->get();

        $cartItems = Cart::where('user_id', $userId)->with('product')->get();
        $sessionCart = session('cart', []);
        $followTotal = $followOrders->sum('total_amount');

        // 💰 金額結算邏輯
        $requestTotal = $requestLists->sum('agent_quote_total');
        $subtotal = $followTotal + $requestTotal;
        $total = ($subtotal > 0) ? ($subtotal + 60) : 0;
        
        $logistics = DB::table('logistics')->where('status', 1)->get();

        return view('shop.shoppingcart', compact(
            'cartItems', 
            'requestLists', 
            'sessionCart', 
            'subtotal', 
            'followOrders',
            'total',
            'logistics'
        ));
    }

    public function cancelOrder($id)
{
    $userId = \Auth::id();

    // 1. 先用傳過來的 ID 找到「其中一筆」訂單，藉此拿到開團貼文 ID (source_id)
    $baseOrder = \App\Models\Order::where('id', $id)
    // 1. 找到該筆訂單，僅允許尚未付款的跟單訂單取消
    $order = \App\Models\Order::where('id', $id)
                ->where('buyer_id', $userId)
                ->where('status', 'pending_payment')
                ->first();

    if ($baseOrder) {
        $sourceId = $baseOrder->source_id;

        // 2. 🔍 撈出「這整組」相同貼文、同個買家、且都是待付款的所有訂單
        $allOrdersInGroup = \App\Models\Order::where('buyer_id', $userId)
                                ->where('source_id', $sourceId)
                                ->where('status', 'pending_payment')
                                ->get();

        // 3. 計算這整組訂單的「總金額」是多少
        $groupTotalAmount = $allOrdersInGroup->sum('total_amount');

        // 4. 去商品表撈出單價，用來反推「這整組總共買了幾件」
        $product = \DB::table('post_products')
                    ->where('agent_post_id', $sourceId)
                    ->first();

        // 預設退回筆數（如果沒撈到單價，就用訂單筆數當作件數）
        $returnQty = $allOrdersInGroup->count(); 

        if ($product && isset($product->price) && $product->price > 0) {
            // 用「整組總金額 / 單價」精準推算這次要還回去的總數量！
            $returnQty = (int) ($groupTotalAmount / $product->price);
        }

        // 5. 🛠️ 一口氣把所有名額全部加回 max_quantity
        \DB::table('post_products')
            ->where('agent_post_id', $sourceId)
            ->increment('max_quantity', $returnQty);

        // 6. ❌ 刪除「這整組」的所有訂單，不再留尾巴
        \App\Models\Order::where('buyer_id', $userId)
            ->where('source_id', $sourceId)
            ->where('status', 'pending_payment')
            ->delete();

        return back()->with('success', "已成功移除該項目，並一次釋出全部共 {$returnQty} 個名額。");

    if ($order) {
        // 2. 逐筆還原訂單明細對應的 sold_quantity
        $returnedQty = 0;

        foreach ($order->items as $item) {
            $quantity = $item->quantity ?? 1;
            $returnedQty += $quantity;

            $product = PostProduct::find($item->product_id);
            if ($product) {
                $product->sold_quantity = max(0, ($product->sold_quantity ?? 0) - $quantity);
                $product->save();
            }
        }

        // 3. 刪除訂單
        $order->delete();

        return back()->with('success', "已移除項目，並釋出 {$returnedQty} 個名額。請購人移除後已還原該商品可跟單數量。");

    }

    return back()->with('error', '找不到該項目或該訂單無法移除。');
}
    public function processCheckout(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'logistics_id' => 'required',
        ]);

        $userId = Auth::id();

        DB::transaction(function () use ($userId) {
            // 將「跟單」與「報價單」狀態轉為 completed
            Order::where('buyer_id', $userId)
                ->where('status', 'pending_payment')
                ->update(['status' => 'completed']);

            DB::table('request_lists')
                ->where('user_id', $userId)
                ->where('status', 'matched')
                ->update(['status' => 'completed']);
        });

        // 💡 修正：為避免路由定義名稱衝突，統一改用安全的 back() 刷新或指定確切路徑
        return back()->with('success', '結帳完成！訂單狀態已轉為已完成。');
    }

   public function addFollowOrder(Request $request)
{
    $userId = Auth::id();
    
    // 🔍 抓賊測試：直接把前端傳過來的「所有欄位」噴出來看
    // 網頁上點擊「跟單/購買」後，如果噴出黑底白字，請截圖給我，或者看裡面有沒有 source_id 或 agent_post_id 欄位！
    dd($request->all()); 

    // 以下是原本的邏輯（暫時不會執行到）
    $sourceId = $request->input('source_id') ?? $request->input('agent_post_id'); 
    $price = $request->input('price', 0);       
    $buyQty = $request->input('quantity', 1); 

    $existingOrder = Order::where('buyer_id', $userId)
                        ->where('source_id', $sourceId)
                        ->where('status', 'pending_payment') 
                        ->first();

    if ($existingOrder) {
        $currentQty = ($price > 0) ? (int)($existingOrder->total_amount / $price) : 1;
        $newQty = $currentQty + $buyQty; 
        $existingOrder->update([
            'items_total'  => $price * $newQty,
            'total_amount' => $price * $newQty, 
        ]);
        DB::table('post_products')->where('agent_post_id', $sourceId)->decrement('max_quantity', $buyQty); 
        return back()->with('success', '已合併數量！');
    } else {
        Order::create([
            'buyer_id'     => $userId,
            'seller_id'    => $request->input('seller_id'),
            'source_id'    => $sourceId,
            'source_type'  => 'App\Models\AgentPost',
            'items_total'  => $price * $buyQty,
            'total_amount' => $price * $buyQty,
            'status'       => 'pending_payment',
        ]);
        DB::table('post_products')->where('agent_post_id', $sourceId)->decrement('max_quantity', $buyQty);
        return back()->with('success', '已成功加入！');
    }

    // 使用 Transaction 確保所有狀態同時更新成功
    \DB::transaction(function () use ($userId) {
        
        // 2. 將「跟單」狀態轉為 wait-for-ship（等待出貨）
        \App\Models\Order::where('buyer_id', $userId)
            ->where('status', 'pending_payment')
            ->update(['status' => 'wait-for-ship']);
        // 3. 將「報價單」狀態轉為 wait-for-ship
        \DB::table('request_lists')
            ->where('user_id', $userId)
            ->where('status', 'matched')
            ->update(['status' => 'wait-for-ship']);
            
        // 4. (選填) 如果有需要紀錄收件地址，可以在這裡處理
    });

    return redirect()->route('shopping.cart')->with('success', '結帳完成！請託單狀態已轉為等待出貨。');
}
}