<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use App\Models\PostProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
@@ -54,50 +55,60 @@ public function store(Request $request)
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


    public function image(PostProduct $postProduct)
    {
        if (! $postProduct->image_path || ! Storage::disk('public')->exists($postProduct->image_path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($postProduct->image_path));
    }

    public function update(Request $request, AgentPost $agentPost)
    {
        abort_unless($agentPost->user_id === Auth::id(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'in:日本,韓國,美國,英國'],
            'description' => ['required', 'string', 'max:2000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'products' => ['required', 'array', 'min:1', 'max:5'],
            'products.*.id' => ['nullable', 'integer'],
            'products.*.name' => ['required', 'string', 'max:255'],
            'products.*.price' => ['required', 'numeric', 'min:0'],
            'products.*.max_quantity' => ['required', 'integer', 'min:1'],
            'products.*.image' => ['nullable', 'image', 'max:2048'],
            'products.*.existing_image' => ['nullable', 'string', 'max:255'],
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

        DB::transaction(function () use ($request, $agentPost, $validated) {
            $agentPost->update([
                'title' => $validated['title'],
                'country' => $validated['country'],
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);

            $existingProducts = $agentPost->products()->get()->keyBy('id');
            $keptIds = [];

            foreach ($validated['products'] as $index => $product) {
                $productId = isset($product['id']) ? (int) $product['id'] : null;
                $model = $productId ? $existingProducts->get($productId) : null;

                $imagePath = $model?->image_path ?? ($product['existing_image'] ?? null);

                if ($request->hasFile("products.$index.image")) {
                    if ($model?->image_path) {
                        Storage::disk('public')->delete($model->image_path);
                    }
                    $imagePath = $request->file("products.$index.image")->store('agent-post-products', 'public');
                }

                if ($model) {
                    $model->update([
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'max_quantity' => $product['max_quantity'],
                        'image_path' => $imagePath,
                    ]);
                    $keptIds[] = $model->id;
                } else {
                    $created = $agentPost->products()->create([
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'max_quantity' => $product['max_quantity'],
                        'image_path' => $imagePath,
                        'currency' => 'TWD',
                        'is_active' => true,
                    ]);
                    $keptIds[] = $created->id;
                }
            }

            $toDelete = $existingProducts->keys()->diff($keptIds);
            foreach ($toDelete as $deleteId) {
                $product = $existingProducts->get($deleteId);
                if ($product?->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $product?->delete();
            }
        });

        return redirect()->route('agent.member')->with('status', '代購貼文已更新！');
    }

}
