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
        $validated = $this->validatePost($request);

        DB::transaction(function () use ($request, $validated) {
            $agentPost = AgentPost::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'country' => $validated['country'],
                'city' => $validated['city'] ?? null,
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'draft',
            ]);

            $this->syncProducts($request, $agentPost, $validated['products']);
        });

        return redirect()->route('agent.member')->with('status', '代購貼文已儲存。');
    }

    public function image(PostProduct $postProduct)
    {
        $resolvedPath = $postProduct->resolveStoredImagePath();

        if (! $resolvedPath) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($resolvedPath));
    }

    public function update(Request $request, AgentPost $agentPost)
    {
        abort_unless($agentPost->user_id === Auth::id(), 403);

        if ($agentPost->status !== 'draft') {
            return redirect()->route('agent.member')->with('status', '僅編輯中的代購貼文可修改。');
        }

        $validated = $this->validatePost($request, true);

        DB::transaction(function () use ($request, $agentPost, $validated) {
            $agentPost->update([
                'title' => $validated['title'],
                'country' => $validated['country'],
                'description' => $validated['description'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);

            $this->syncProducts($request, $agentPost, $validated['products'], true);
        });

        return redirect()->route('agent.member')->with('status', '代購貼文已更新！');
    }

    public function submit(AgentPost $agentPost)
    {
        abort_unless($agentPost->user_id === Auth::id(), 403);

        if ($agentPost->status !== 'draft') {
            return redirect()->route('agent.member')->with('status', '僅編輯中的代購貼文可送出。');
        }

        if (! $agentPost->products()->exists()) {
            return redirect()->route('agent.member')->with('status', '請至少保留 1 項商品後再送出貼文。');
        }

        $agentPost->update([
            'status' => 'open',
        ]);

        return redirect()->route('agent.member')->with('status', '代購貼文已送出並上架！');
    }

    public function destroy(AgentPost $agentPost)
    {
        abort_unless($agentPost->user_id === Auth::id(), 403);

        if ($agentPost->status !== 'draft') {
            return redirect()->route('agent.member')->with('status', '僅編輯中的代購貼文可刪除。');
        }

        DB::transaction(function () use ($agentPost) {
            foreach ($agentPost->products as $product) {
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $product->delete();
            }

            if ($agentPost->cover_image) {
                Storage::disk('public')->delete($agentPost->cover_image);
            }

            $agentPost->delete();
        });

        return redirect()->route('agent.member')->with('status', '代購貼文已刪除。');
    }

    private function validatePost(Request $request, bool $includeExistingImage = false): array
    {
        $rules = [
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
        ];

        if ($includeExistingImage) {
            $rules['products.*.id'] = ['nullable', 'integer'];
            $rules['products.*.existing_image'] = ['nullable', 'string', 'max:255'];
        }

        return $request->validate($rules, [
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
    }

    private function syncProducts(Request $request, AgentPost $agentPost, array $products, bool $updating = false): void
    {
        $existingProducts = $updating ? $agentPost->products()->get()->keyBy('id') : collect();
        $keptIds = [];

        foreach ($products as $index => $product) {
            $productId = $updating && isset($product['id']) ? (int) $product['id'] : null;
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
                continue;
            }

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

        if (! $updating) {
            return;
        }

        $toDelete = $existingProducts->keys()->diff($keptIds);
        foreach ($toDelete as $deleteId) {
            $product = $existingProducts->get($deleteId);
            if ($product?->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product?->delete();
        }
    }
}