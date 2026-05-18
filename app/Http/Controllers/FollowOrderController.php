<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use App\Models\PostProduct;
use App\Models\FollowOrder; // 確保你有建立這個 Model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowOrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. 驗證傳入欄位
        $request->validate([
            'agent_post_id'   => 'required|exists:agent_posts,id',
            'post_product_id' => 'required|exists:post_products,id',
            'quantity'        => 'required|integer|min:1',
        ]);

        // 2. 獲取商品資訊以確保價格正確（不從前端傳價格，防止被竄改）
        $product = PostProduct::findOrFail($request->post_product_id);
        $quantity = $request->quantity;

        // 3. 建立跟單紀錄 (包含你要求的所有欄位)
        $followOrder = FollowOrder::create([
            'user_id'         => Auth::id(),           // 誰在跟單
            'agent_post_id'   => $request->agent_post_id,
            'post_product_id' => $request->post_product_id,
            'price'           => $product->price,      // 商品單價
            'quantity'        => $quantity,            // 數量
            'total_amount'    => $product->price * $quantity, // 總金額
            'status'          => 'matched',            // 直接設為 matched 進入結帳流程
        ]);

        // 4. 判斷回傳方式
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('cart.index') // 跳轉到結帳頁面的路由名稱
            ]);
        }

        return redirect()->route('cart.index')->with('success', '已加入結帳區！');
    }
}