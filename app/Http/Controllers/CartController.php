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
use App\Models\OrderItem;

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
    // 1. 尋找要刪除的訂單
    $order = Order::where('id', $id)
                  ->where('buyer_id', auth()->id())
                  ->firstOrFail();

    try {
        \DB::transaction(function () use ($order) {
            
            // 2. 循環這筆訂單裡的所有商品，回扣已售數量
            foreach ($order->items as $item) {
                $product = \App\Models\PostProduct::find($item->product_id);
                
                if ($product) {
                    // 防護鎖：防止扣成負數
                    $newSoldQuantity = $product->sold_quantity - $item->quantity;
                    if ($newSoldQuantity < 0) {
                        $newSoldQuantity = 0;
                    }
                    
                    $product->update([
                        'sold_quantity' => $newSoldQuantity
                    ]);
                }
            }

            // 🎯 核心關鍵修正：在刪除主訂單前，先把這筆訂單底下的所有明細清空！
            // 這樣 order_items 資料表裡面的東西就會一併被處理掉
            $order->items()->delete(); 

            // 3. 執行主訂單的刪除
            $order->delete(); 
        });

        return redirect()->route('shopping.cart')->with('success', '已成功移除該項跟單商品及明細！');

    } catch (\Exception $e) {
        return redirect()->route('shopping.cart')->with('error', '移除失敗，原因：' . $e->getMessage());
    }
}
    public function processCheckout(Request $request)
{
    $request->validate([
        'address'        => 'required|string',
        'logistics_id'   => 'required',
        'payment_method' => 'required|string',
    ]);

    $userId        = Auth::id();
    $paymentMethod = $request->input('payment_method');

    // 勾選的跟單訂單 ID
    $followOrderIds = array_filter(
        explode(',', $request->selected_follow_orders)
    );

    // 勾選的請購單 ID
    $requestListIds = array_filter(
        explode(',', $request->selected_request_lists)
    );

    DB::transaction(function () use (
        $userId,
        $paymentMethod,
        $followOrderIds,
        $requestListIds
    ) {

        /*
        |--------------------------------------------------------------------------
        | 只更新勾選的跟單
        |--------------------------------------------------------------------------
        */

        if (!empty($followOrderIds)) {

            Order::where('buyer_id', $userId)

                ->whereIn('id', $followOrderIds)

                ->where('status', 'pending_payment')

                ->update([

                    'status'         => 'wait-for-ship',

                    'payment_method' => $paymentMethod,
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 只更新勾選的請購單
        |--------------------------------------------------------------------------
        */

        if (!empty($requestListIds)) {

            DB::table('request_lists')

                ->where('user_id', $userId)

                ->whereIn('id', $requestListIds)

                ->where('status', 'matched')

                ->update([
                    'status' => 'wait-for-ship'
                ]);
        }
    });

    return back()->with(
        'success',
        '已成功結帳勾選商品！'
    );
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
public function update(Request $request, $id)
{
   // 1. 驗證前端傳過來的數量（允許 0，0 代表移除）
    $request->validate([
        'quantity' => 'required|integer|min:0',
    ]);

    // 2. 精準撈出這一筆訂單明細 (order_items.id)
    $item = OrderItem::with('order')->findOrFail($id);
    
    // 3. 撈出該明細對應的商品
    $product = PostProduct::find($item->product_id);

    $oldQuantity = $item->quantity;
    $newQuantity = intval($request->quantity);
    
    // 計算這一次加減的「差值」
    $diff = $newQuantity - $oldQuantity;

    // 4. 透過資料庫交易處理，確保所有數字同步更新
    $resultMessage = DB::transaction(function () use ($item, $product, $newQuantity, $oldQuantity, $diff) {
        if ($product) {
            $product = PostProduct::where('id', $product->id)->lockForUpdate()->first();
        }

        if ($newQuantity === 0) {
            if ($product) {
                $product->sold_quantity = max(0, (int) $product->sold_quantity - $oldQuantity);
                $product->save();
            }

            $order = $item->order;
            $item->delete();

            if ($order) {
                if ($order->items()->count() === 0) {
                    $order->delete();
                } else {
                    $order->items_total = $order->items()->sum('subtotal');
                    $order->total_amount = $order->items_total + ($order->shipping_fee ?? 0) + ($order->platform_fee ?? 0);
                    $order->save();
                }
            }

            return '商品已從跟單中移除。';
        }

        if ($product && $diff > 0) {
            $remaining = max(0, (int) $product->max_quantity - (int) $product->sold_quantity);
            $allowedMax = $oldQuantity + $remaining;
            if ($newQuantity > $allowedMax) {
                return '超過可下單數量，目前最多可調整為 ' . $allowedMax . ' 件。';
            }
        }
        $item->quantity = $newQuantity;
        $item->subtotal = $item->price * $newQuantity;
        $item->save();

        // 連動更新商品表的已售數量 (sold_quantity)，並加上防負數安全機制
        if ($product) {
             $product->sold_quantity = max(0, (int) $product->sold_quantity + $diff);
            $product->save();
        }

        // 重新計算主訂單的總金額
        $order = $item->order;
        if ($order) {
            // 🎯 修正：sum() 裡面改抓 'subtotal' 欄位
            $order->items_total = $order->items()->sum('subtotal');
            
            // 這裡會加上運費、平台費等（如果有的話，沒有就直接等於 items_total）
            $order->total_amount = $order->items_total + ($order->shipping_fee ?? 0) + ($order->platform_fee ?? 0);
            $order->save();
        }

         return '數量與總金額已精準更新！';
    });

    // 5. 成功後返回，並帶上成功的 Session 訊息
    return redirect()->back()->with('success', $resultMessage);
}
}