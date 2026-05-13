<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\RequestList;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class CartController extends Controller
{
public function index()
{
    $userId = \Auth::id();

    $followOrders = \App\Models\Order::where('buyer_id', $userId)
                        ->where('status', 'pending_payment')
                        ->with(['source', 'seller']) // 這裡會自動去抓 User 表的資料
                        ->get();

    // 修正：將篩選條件改為 'matched'，以符合資料庫目前的狀態
    // 將原本的 where('status', 'matched') 改成這行測試
        // 如果你有用 SoftDeletes，試試這行
    $requestLists = \App\Models\RequestList::where('user_id', $userId)
                        ->where('status', 'matched')
                        ->whereNotNull('people') // 如果沒有人報價，people 會是 null，這行會將其過濾掉
                        ->with('agent')          // 預加載 People 模型資料
                        ->get();
    $cartItems = \App\Models\Cart::where('user_id', $userId)->with('product')->get();
    $sessionCart = session('cart', []);
    $followTotal = $followOrders->sum('total_amount');

    // 修正：計算總額時使用 budget_total 欄位
    $subtotal = $cartItems->sum(fn($item) => $item->quantity * ($item->product->price ?? 0));
    $requestTotal = $requestLists->sum('agent_quote_total');
    
    
    $subtotal = $followTotal + $requestTotal ;
    $total = ($subtotal > 0) ? ($subtotal + 100) : 0;

    return view('shop.shoppingcart', compact(
        'cartItems', 
        'requestLists', 
        'sessionCart', 
        'subtotal', 
        'followOrders',
        'total'
    ));
}
}