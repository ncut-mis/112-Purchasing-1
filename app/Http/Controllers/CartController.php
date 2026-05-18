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
        $userId = Auth::id();

        $baseOrder = Order::where('id', $id)
                        ->where('buyer_id', $userId)
                        ->first();

        if ($baseOrder) {
            $sourceId = $baseOrder->source_id;

            DB::transaction(function () use ($userId, $sourceId) {
                // 撈出同一貼文、同一請購人、待付款的全部跟單
                $allOrdersInGroup = Order::where('buyer_id', $userId)
                    ->where('source_id', $sourceId)
                    ->where('status', 'pending_payment')
                    ->with('items')
                    ->get();

                // 逐筆 item 回補 sold_quantity（最精準）
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

                // 刪除整組待付款跟單
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
            // 將「跟單」狀態改為等待出貨
            Order::where('buyer_id', $userId)
                ->where('status', 'pending_payment')
                ->update(['status' => 'wait-for-ship']);

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