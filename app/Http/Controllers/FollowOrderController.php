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
    // 1. 偵錯日誌：幫你看看到底收到了什麼
    \Log::info('跟單請求資料:', $request->all());

    // 2. 靈活驗證：判斷是單一傳入還是陣列傳入
    $items = [];
    if ($request->has('items')) {
        // 處理批次陣列傳入 (來自 Modal 表格)
        foreach ($request->items as $productId => $data) {
            $qty = (int)($data['quantity'] ?? 0);
            if ($qty > 0) {
                $items[] = ['product_id' => $productId, 'quantity' => $qty];
            }
        }
    } else {
        // 處理單一傳入 (來自舊的購物車按鈕)
        $items[] = ['product_id' => $request->post_product_id, 'quantity' => $request->quantity];
    }

    if (empty($items)) {
        return back()->withErrors('請至少選擇一個商品');
    }

    // 3. 批次寫入資料庫
    foreach ($items as $item) {
        $product = PostProduct::findOrFail($item['product_id']);
        
        FollowOrder::create([
            'user_id'         => Auth::id(),
            'agent_post_id'   => $request->agent_post_id,
            'post_product_id' => $item['product_id'],
            'price'           => $product->price,
            'quantity'        => $item['quantity'],
            'total_amount'    => $product->price * $item['quantity'],
            'status'          => 'matched',
        ]);
    }

    return redirect()->route('cart.index')->with('success', '已加入結帳區！');
}
}