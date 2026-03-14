<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgentPostController extends Controller
{
    public function create()
    {
        return view('agent.posts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'in:日本,韓國,美國,英國'],
            'city' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:2000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'products' => ['required', 'array', 'min:1', 'max:5'],
            'products.*.name' => ['required', 'string', 'max:255'],
            'products.*.price' => ['required', 'numeric', 'min:0'],
            'products.*.max_quantity' => ['required', 'integer', 'min:1'],
            'products.*.image' => ['nullable', 'image', 'max:2048'],
        ], [
            'title.required' => '請填寫貼文標題',
            'country.required' => '請選擇代購地區',
            'country.in' => '代購地區僅支援日本、韓國、美國、英國',
            'description.required' => '請填寫描述訊息',
            'start_date.required' => '請選擇銷售開始日期',
            'end_date.required' => '請選擇銷售結束日期',
            'end_date.after_or_equal' => '銷售結束日期不可早於開始日期',
            'products.required' => '至少需要 1 項商品',
            'products.max' => '商品最多可輸入 5 項',
            'products.*.name.required' => '請填寫商品名稱',
            'products.*.price.required' => '請填寫商品單價',
            'products.*.max_quantity.required' => '請填寫商品最高數量',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $agentPost = AgentPost::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'country' => $validated['country'],
                'city' => $validated['city'] ?? null,
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'open',
            ]);

            foreach ($validated['products'] as $index => $product) {
                $imagePath = null;
                if ($request->hasFile("products.$index.image")) {
                    $imagePath = $request->file("products.$index.image")->store('agent-post-products', 'public');
                }

                $agentPost->products()->create([
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'max_quantity' => $product['max_quantity'],
                    'image_path' => $imagePath,
                    'currency' => 'TWD',
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('agent.member')->with('status', '代購貼文已成功發布！');
    }
}
