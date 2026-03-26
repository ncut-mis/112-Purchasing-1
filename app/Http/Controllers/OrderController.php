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
        if ((int) $request->user()->id === (int) $agentPost->user_id) {
            return back()->withErrors(['follow_order' => '不能跟自己的貼文下單。']);
        }

        if ($agentPost->status !== 'open') {
            return back()->withErrors(['follow_order' => '此貼文目前不開放跟單。']);
        }

        $validated = $request->validate([
            'products' => ['required', 'array'],
            'products.*.quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        $productsById = $agentPost->products()
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $selectedItems = [];
        $totalQty = 0;
        $itemsTotal = 0.0;
        $currency = 'TWD';

        foreach ($validated['products'] as $productId => $row) {
            $quantity = (int) ($row['quantity'] ?? 0);
            if ($quantity < 1) {
                continue;
            }

            $product = $productsById->get((int) $productId);
            if (! $product) {
                continue;
            }

            $maxQuantity = $product->max_quantity;
            $soldQuantity = (int) ($product->sold_quantity ?? 0);
            $remainingQuantity = is_null($maxQuantity) ? null : max(0, (int) $maxQuantity - $soldQuantity);

            if (! is_null($remainingQuantity) && $quantity > $remainingQuantity) {
                return back()->withErrors([
                    'follow_order' => "商品「{$product->name}」剩餘可跟單數量不足。",
                ]);
            }

            $price = (float) $product->price;
            $subtotal = $price * $quantity;

            $totalQty += $quantity;
            $itemsTotal += $subtotal;
            $currency = $product->currency ?: $currency;

            $selectedItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $subtotal,
            ];
        }

        if ($totalQty < 1) {
            return back()->withErrors(['follow_order' => '請至少選擇一項商品數量後再確認結帳。']);
        }

        DB::transaction(function () use ($request, $agentPost, $selectedItems, $itemsTotal, $currency) {
            $buyer = $request->user();

            $order = Order::create([
                'order_no' => Order::generateOrderNo(),
                'buyer_id' => $buyer->id,
                'seller_id' => $agentPost->user_id,
                'source_type' => AgentPost::class,
                'source_id' => $agentPost->id,
                'items_total' => $itemsTotal,
                'shipping_fee' => 0,
                'platform_fee' => 0,
                'total_amount' => $itemsTotal,
                'currency' => $currency,
                'status' => 'pending_payment',
                'recipient_data' => [
                    'name' => $buyer->name,
                    'email' => $buyer->email,
                ],
                'note' => "跟單來源：{$agentPost->title}",
            ]);

            foreach ($selectedItems as $item) {
                $product = $item['product'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'options' => null,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);

                $product->increment('sold_quantity', $item['quantity']);
            }
        });

        return redirect()
            ->route('dashboard', ['section' => 'follow-orders'])
            ->with('status', '跟單成功，已建立訂單並加入跟單紀錄。');
    }
}