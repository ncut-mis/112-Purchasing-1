<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\RequestList;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
public function index()
{
    $userId = \Auth::id();

    // 修正：將篩選條件改為 'matched'，以符合資料庫目前的狀態
    // 將原本的 where('status', 'matched') 改成這行測試
        // 如果你有用 SoftDeletes，試試這行
    $requestLists = \App\Models\RequestList::withTrashed()
                        ->where('user_id', $userId)
                        ->get();
    $cartItems = \App\Models\Cart::where('user_id', $userId)->with('product')->get();
    $sessionCart = session('cart', []);

    // 修正：計算總額時使用 budget_total 欄位
    $subtotal = $cartItems->sum(fn($item) => $item->quantity * ($item->product->price ?? 0));
    $subtotal += $requestLists->sum('agent_quote_total'); 

    $total = $subtotal + 100; // 運費

    return view('shop.shoppingcart', compact(
        'cartItems', 
        'requestLists', 
        'sessionCart', 
        'subtotal', 
        'total'
    ));
}
}