<?php
namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\PostProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        // 取得當前用戶的購物車
        $cartItems = Cart::where('user_id', Auth::id())
                        ->with('product')
                        ->get();
        
        $subtotal = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
        $shipping = 100;
        $total = $subtotal + $shipping;

        return view('shop.shoppingcart', compact('cartItems', 'subtotal', 'shipping', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'post_product_id' => 'required|exists:post_products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = PostProduct::findOrFail($request->post_product_id);
        
        if ($product->stock < $request->quantity) {
            return back()->with('error', '庫存不足！');
        }

        // 檢查用戶購物車中是否已有此商品
        $cartItem = Cart::where('user_id', Auth::id())
                       ->where('post_product_id', $request->post_product_id)
                       ->first();
        
        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'post_product_id' => $request->post_product_id,
                'quantity' => $request->quantity,
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', '已加入購物車！');
    }

    public function update(Request $request, $id)
    {
        $cartItem = Cart::where('user_id', Auth::id())
                       ->where('id', $id)
                       ->firstOrFail();
                       
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $cartItem->product->stock
        ]);

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return back()->with('success', '購物車已更新！');
    }

    public function remove($id)
    {
        $cartItem = Cart::where('user_id', Auth::id())
                       ->where('id', $id)
                       ->firstOrFail();
        $cartItem->delete();

        return back()->with('success', '商品已移除！');
    }
}
