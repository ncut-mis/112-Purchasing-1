<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use App\Models\PostProduct;
use App\Models\Favorite;
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
        $imageData = $postProduct->image_path;

        $headers = [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        if ($imageData && ! $this->isBinaryImageData($imageData)) {
            $resolvedPath = $postProduct->resolveStoredImagePath();

            if ($resolvedPath) {
                return response()->file(Storage::disk('public')->path($resolvedPath), $headers);
            }
        }

        if ($imageData && $this->isBinaryImageData($imageData)) {
            $mime = $this->detectImageMime($imageData) ?? 'image/jpeg';

            return response($imageData, 200, array_merge($headers, [
                'Content-Type' => $mime,
            ]));
        }

        abort(404);
    }

    public function coverImage(AgentPost $agentPost)
    {
        $imageData = $agentPost->cover_image;

        $headers = [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        if ($imageData && ! $this->isBinaryImageData($imageData)) {
            $normalized = $this->normalizeStoragePath($imageData);
            if ($normalized && Storage::disk('public')->exists($normalized)) {
                return response()->file(Storage::disk('public')->path($normalized), $headers);
            }
        }

        if ($imageData && $this->isBinaryImageData($imageData)) {
            $mime = $this->detectImageMime($imageData) ?? 'image/jpeg';

            return response($imageData, 200, array_merge($headers, [
                'Content-Type' => $mime,
            ]));
        }

        abort(404);
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

    // 出貨：將 orders 狀態改為 shipped
    public function ship(AgentPost $agentPost)
    {
        abort_unless($agentPost->user_id === Auth::id(), 403);

        \App\Models\Order::where('seller_id', Auth::id())
            ->where('source_id', $agentPost->id)
            ->where('source_type', \App\Models\AgentPost::class)
            ->where('status', 'pending_payment')
            ->update(['status' => 'shipped']);
        
        $agentPost->update([
            'status' => 'shipped',
        ]);

        return redirect()->route('agent.member')->with('status', '已標記為已出貨！');
    }

    // 完成：將代購貼文標記為已完成，移至歷史紀錄
    public function complete(AgentPost $agentPost)
    {
        abort_unless($agentPost->user_id === Auth::id(), 403);

        if ($agentPost->status !== 'shipped') {
            return redirect()->route('agent.member')->with('status', '僅已出貨的代購貼文可標記為完成');
        }

        $agentPost->update(['status' => 'completed']);

        // 同步將相關訂單也標記為 completed
        \App\Models\Order::where('seller_id', Auth::id())
            ->where('source_id', $agentPost->id)
            ->where('source_type', \App\Models\AgentPost::class)
            ->whereIn('status', ['shipped', 'pending_payment'])
            ->update(['status' => 'completed']);

        return redirect()->route('agent.member')->with('status', '代購貼文已完成，已移至歷史紀錄！');
    }

    // 代購人取消特定買家的訂單並回補數量
    public function cancelBuyerOrder(\Illuminate\Http\Request $request, \App\Models\Order $order)
    {
        abort_unless($order->seller_id === Auth::id(), 403);
        abort_if(!empty($order->paid_at), 403, '已付款訂單不可取消。');

        $sourceId = $order->source_id;

        // 從 order_items 計算數量
        $returnQty = $order->items ? $order->items->sum('quantity') : 0;

        // fallback：用金額反推
        if ($returnQty <= 0) {
            $product = \DB::table('post_products')->where('agent_post_id', $sourceId)->first();
            if ($product && $product->price > 0) {
                $returnQty = (int) round($order->total_amount / $product->price);
            } else {
                $returnQty = 1;
            }
        }

        // 回補 sold_quantity
        if ($returnQty > 0) {
            \DB::table('post_products')
                ->where('agent_post_id', $sourceId)
                ->decrement('sold_quantity', $returnQty);
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('status', '已取消訂單，回補 ' . $returnQty . ' 個名額。');
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
                    $this->deleteStorageFileIfExists($product->image_path);
                }
                $product->delete();
            }

            if ($agentPost->cover_image) {
                $this->deleteStorageFileIfExists($agentPost->cover_image);
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

    private function isBinaryImageData(?string $value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        if (strlen($value) > 255) {
            return true;
        }

        return preg_match('/[\x00-\x08\x0E-\x1F]/', substr($value, 0, 100)) === 1;
    }

    private function normalizeStoragePath(?string $path): ?string
    {
        if (! $path || $this->isBinaryImageData($path)) {
            return null;
        }

        $normalized = ltrim($path, '/');
        $normalized = preg_replace('#^storage/#', '', $normalized);
        $normalized = preg_replace('#^public/#', '', $normalized);

        return $normalized;
    }

    private function deleteStorageFileIfExists(?string $path): void
    {
        $normalized = $this->normalizeStoragePath($path);

        if ($normalized && Storage::disk('public')->exists($normalized)) {
            Storage::disk('public')->delete($normalized);
        }
    }

    private function resolveProductImageData(Request $request, array $product, ?PostProduct $model, int $index): ?string
    {
        if ($request->hasFile("products.$index.image")) {
            return file_get_contents($request->file("products.$index.image")->getRealPath());
        }

        $existingValue = $model?->image_path ?? ($product['existing_image'] ?? null);

        if (! $existingValue) {
            return null;
        }

        if ($this->isBinaryImageData($existingValue)) {
            return $existingValue;
        }

        $storagePath = $this->normalizeStoragePath($existingValue);
        if ($storagePath && Storage::disk('public')->exists($storagePath)) {
            return file_get_contents(Storage::disk('public')->path($storagePath));
        }

        return $existingValue;
    }

    private function detectImageMime(string $imageData): ?string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if (! $finfo) {
            return null;
        }

        $mime = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);

        return $mime ?: null;
    }

    private function syncProducts(Request $request, AgentPost $agentPost, array $products, bool $updating = false): void
    {
        $existingProducts = $updating ? $agentPost->products()->get()->keyBy('id') : collect();
        $keptIds = [];

        foreach ($products as $index => $product) {
            $productId = $updating && isset($product['id']) ? (int) $product['id'] : null;
            $model = $productId ? $existingProducts->get($productId) : null;

            $imageData = $this->resolveProductImageData($request, $product, $model, $index);

            if ($request->hasFile("products.$index.image")) {
                $this->deleteStorageFileIfExists($model?->image_path);
            }

            if ($model) {
                $model->update([
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'max_quantity' => $product['max_quantity'],
                    'image_path' => $imageData,
                ]);
                $keptIds[] = $model->id;
                continue;
            }

            $created = $agentPost->products()->create([
                'name' => $product['name'],
                'price' => $product['price'],
                'max_quantity' => $product['max_quantity'],
                'image_path' => $imageData,
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
            $this->deleteStorageFileIfExists($product?->image_path);
            $product?->delete();
        }
    }
        public function index(Request $request)
{
    // 1. 初始化查詢，預載入 user 與 products 關聯
    $query = AgentPost::where('status', 'open')
                ->with(['user', 'products']);

    // 2. 寬鬆搜尋邏輯
    if ($postId = $request->input('post_id')) {
        $query->where('id', $postId);
    } 
    // 2. 這裡處理關鍵字搜尋
    elseif ($search = $request->input('search')) {
        $searchTerm = "%{$search}%";
        $query->where(function($q) use ($searchTerm) {
            $q->where('title', 'like', $searchTerm)
              ->orWhere('description', 'like', $searchTerm);
        });
    }

    // 3. 地區篩選
    if ($request->filled('country')) {
        $query->where('country', $request->country);
    }

    // 4. 執行分頁，變數名稱維持 $posts
    $posts = $query->latest()
                   ->paginate(12)
                   ->withQueryString();

    $favoritedAgentPostIds = Auth::check()
        ? Favorite::query()
            ->where('user_id', Auth::id())
            ->where('favoriteable_type', AgentPost::class)
            ->pluck('favoriteable_id')
            ->map(fn ($id) => (int) $id)
            ->all()
        : [];

    return view('store.index', compact('posts', 'favoritedAgentPostIds'));
}
}