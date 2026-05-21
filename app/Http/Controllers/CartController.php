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

        $baseOrder = \App\Models\Order::where('id', $id)
                    ->where('buyer_id', $userId)
                    ->first();

        if ($baseOrder) {
            $sourceId = $baseOrder->source_id;

            // 撈出同貼文、同買家、待付款的所有訂單
            $allOrdersInGroup = \App\Models\Order::where('buyer_id', $userId)
                                    ->where('source_id', $sourceId)
                                    ->where('status', 'pending_payment')
                                    ->with('items')
                                    ->get();

            // 用 order_items 計算實際購買數量（最準確）
            $returnQty = $allOrdersInGroup->sum(function ($order) {
                return $order->items->sum('quantity');
            });

            // 如果沒有 items 資料，用金額反推
            if ($returnQty <= 0) {
                $groupTotalAmount = $allOrdersInGroup->sum('total_amount');
                $product = \DB::table('post_products')
                            ->where('agent_post_id', $sourceId)
                            ->first();
                if ($product && isset($product->price) && $product->price > 0) {
                    $returnQty = (int) round($groupTotalAmount / $product->price);
                } else {
                    $returnQty = $allOrdersInGroup->count();
                }
            }

            // 修正：回補 sold_quantity 而非 max_quantity
            if ($returnQty > 0) {
                \DB::table('post_products')
                    ->where('agent_post_id', $sourceId)
                    ->decrement('sold_quantity', $returnQty);
            }

            // 刪除整組訂單
            \App\Models\Order::where('buyer_id', $userId)
                ->where('source_id', $sourceId)
                ->where('status', 'pending_payment')
                ->delete();

            return back()->with('success', "已成功移除該項目，釋出 {$returnQty} 個名額。");
        }

        return back()->with('error', '找不到該項目。');
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

        DB::transaction(function () use ($userId, $paymentMethod) {
            // 將「跟單」狀態改為等待出貨，並記錄付款方式
            Order::where('buyer_id', $userId)
                ->where('status', 'pending_payment')
                ->update([
                    'status'         => 'wait-for-ship',
                    'payment_method' => $paymentMethod,
                ]);

            // 將「專屬代購報價單」請託清單狀態改為等待出貨
            DB::table('request_lists')
                ->where('user_id', $userId)
                ->where('status', 'matched')
                ->update(['status' => 'wait-for-ship']);
        });

        return back()->with('success', '結帳完成！跟單與請託清單狀態已更新為等待出貨。');
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