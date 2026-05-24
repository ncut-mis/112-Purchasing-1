<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request, AgentPost $agentPost)
    {
        // 1. 權限與狀態檢查
        if ((int) $request->user()->id === (int) $agentPost->user_id) {
            return back()->withErrors(['follow_order' => '不能跟自己的貼文下單。']);
        }

        if ($agentPost->status !== 'open') {
            return back()->withErrors(['follow_order' => '此貼文目前不開放跟單。']);
        }

        // 2. 驗證前端傳來的 products 陣列
        $validated = $request->validate([
            'products' => ['required', 'array'],
            'products.*.quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        $productsById = $agentPost->products()->where('is_active', true)->get()->keyBy('id');
        $selectedItems = [];
        $totalQty = 0;
        $itemsTotal = 0.0;
        $currency = 'TWD';

        foreach ($validated['products'] as $productId => $row) {
            $quantity = (int) ($row['quantity'] ?? 0);
            if ($quantity < 1) continue;

            $product = $productsById->get((int) $productId);
            if (! $product) continue;

            // 檢查庫存
            $remaining = is_null($product->max_quantity) ? null : max(0, (int) $product->max_quantity - (int) $product->sold_quantity);
            if (! is_null($remaining) && $quantity > $remaining) {
                return back()->withErrors(['follow_order' => "商品「{$product->name}」剩餘可跟單數量不足。"]);
            }

            $subtotal = (float)$product->price * $quantity;
            $totalQty += $quantity;
            $itemsTotal += $subtotal;

            $selectedItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'price' => (float)$product->price,
                'subtotal' => $subtotal,
            ];
        }

        if ($totalQty < 1) return back()->withErrors(['follow_order' => '請至少選擇一項商品。']);

        // 3. 執行交易寫入資料庫
        DB::transaction(function () use ($request, $agentPost, $selectedItems, $itemsTotal, $currency) {
            $buyer = $request->user();

           $order = Order::create([
                'order_no' => 'ORD-' . time() . '-' . $buyer->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $agentPost->user_id,
                'source_type' => AgentPost::class,
                'source_id' => $agentPost->id,
                
                // 這裡務必同時指定兩個欄位，確保資料庫不會報錯
                'total_amount' => $itemsTotal, 
                'items_total'  => $itemsTotal, 
                
                'currency' => $currency,
                'status' => 'pending_payment',
                'recipient_data' => ['name' => $buyer->name, 'email' => $buyer->email],
                'note' => "跟單來源：{$agentPost->title}",
            ]);

            foreach ($selectedItems as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'name' => $item['product']->name,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
                $item['product']->increment('sold_quantity', $item['quantity']);
            }
        });

        return redirect()->route('cart.index')->with('status', '跟單成功！');
    }
    public function cancel(Request $request, Order $order)
    {
        // 只有賣家（代購人）可以取消
        if ((int) $order->seller_id !== (int) $request->user()->id) {
            abort(403, '你沒有權限取消這筆跟單。');
        }

        // 只有未付款的訂單才能取消
        if ($order->status !== 'pending_payment' || !is_null($order->paid_at)) {
            return back()->with('error', '此訂單已付款或無法取消。');
        }

        DB::transaction(function () use ($order) {
            // 把 sold_quantity 回補
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    \App\Models\PostProduct::where('id', $item->product_id)
                        ->decrement('sold_quantity', $item->quantity);
                }
            }

            $order->update(['status' => 'cancelled']);
        });

        return back()->with('status', '已成功取消該跟單。');
    }

    public function complete(Request $request, Order $order)
    {
        if ((int) $order->buyer_id !== (int) $request->user()->id) {
            abort(403, '你沒有權限完成這筆跟單。');
        }

        if (in_array($order->status, ['completed', 'cancelled', 'refunded'], true)) {
            return redirect()
                ->route('dashboard', ['section' => 'follow-orders'])
                ->with('status', '這筆跟單已是結案狀態。');
        }

        $order->update([
            'status' => 'completed',
        ]);

        return redirect()
            ->route('dashboard', ['section' => 'follow-orders'])
            ->with('status', '跟單已標記為完成，已移至歷史紀錄。');
    }
}