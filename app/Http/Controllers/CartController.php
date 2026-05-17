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
                ->where('buyer_id', $userId)
                ->first();

    if ($baseOrder) {
        $sourceId = $baseOrder->source_id;

        DB::transaction(function () use ($userId, $sourceId) {
            // 2. 撈出同一貼文、同一請購人、待付款的全部跟單
            $allOrdersInGroup = Order::where('buyer_id', $userId)
                ->where('source_id', $sourceId)
                ->where('status', 'pending_payment')
                ->with('items')
                ->get();

            // 3. 回補 sold_quantity（僅減少 sold_quantity，不變動 max_quantity）
            foreach ($allOrdersInGroup as $order) {
                foreach ($order->items as $item) {
                    if (! $item->product_id || (int) $item->quantity <= 0) {
                        continue;
                    }

                    PostProduct::where('id', $item->product_id)
                        ->decrement('sold_quantity', (int) $item->quantity);

                    // 避免 sold_quantity 因舊資料異常而變負數
                    PostProduct::where('id', $item->product_id)
                        ->where('sold_quantity', '<', 0)
                        ->update(['sold_quantity' => 0]);
                }
            }

            // 4. 移除整組待付款跟單
            Order::where('buyer_id', $userId)
                ->where('source_id', $sourceId)
                ->where('status', 'pending_payment')
                ->delete();
        });

        return back()->with('success', '已成功移除該項目，並回補商品的已售數量。');
    }

    return back()->with('error', '找不到該項目。');
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
}
}