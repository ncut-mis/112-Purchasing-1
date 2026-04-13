<?php

namespace App\Http\Controllers;

use App\Models\RequestItem;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Favorite;

class RequestListController extends Controller
{
    public function create()
    {
        return view('request-list.create');
    }

    public function store(Request $request)
    {
       $today = Carbon::today()->toDateString();
       $maxDeadline = Carbon::today()->addMonth()->toDateString();

        $validated = $request->validate([
            'country' => ['required', 'string', 'max:255'],
            'deadline' => ['required', 'date', "after_or_equal:{$today}", "before_or_equal:{$maxDeadline}"],
            'store_name' => ['nullable', 'string', 'max:255'],
            'detail_address' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:3'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.item_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $firstItemName = $validated['items'][0]['item_name'] ?? '未命名商品';

        $requestList = RequestList::create([
            'user_id' => Auth::id(),
            'title' => $validated['store_name'] ?: $firstItemName,
            'items' => json_encode($validated['items'] ?? []),
            'country' => $validated['country'],
            'city' => null,
            'deadline' => $validated['deadline'],
            'budget_total' => null,
            'currency' => 'TWD',
            'status' => 'editing',
            'detail_address' => $validated['detail_address'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);


        foreach ($validated['items'] as $index => $item) {
            $imagePath = null;
            if ($request->hasFile("items.$index.item_image")) {
                $imagePath = $request->file("items.$index.item_image")->store('request-items', 'public');
            }

            RequestItem::create([
                'request_list_id' => $requestList->id,
                'name' => $item['item_name'],
                'quantity' => $item['quantity'],
                'reference_image' => $imagePath,
                'reference_url' => null,
                'expected_price' => null,
                'specification' => null,
            ]);
        }

        return redirect()->route('dashboard')->with('status', '請購清單建立成功');
    }

    public function update(Request $request, RequestList $requestList)
    {
        abort_unless($requestList->user_id === Auth::id(), 403);

        if ($requestList->status !== 'editing') {
            return redirect()->route('dashboard')->with('status', '僅編輯中的請購清單可修改');
        }

         $createdDate = $requestList->created_at
            ? $requestList->created_at->copy()->startOfDay()->toDateString()
            : Carbon::today()->toDateString();
        $maxDeadline = Carbon::parse($createdDate)->addMonth()->toDateString();

        $validated = $request->validate([
            'country' => ['required', 'string', 'max:255'],
            'deadline' => ['required', 'date', "after_or_equal:{$createdDate}", "before_or_equal:{$maxDeadline}"],
            'store_name' => ['nullable', 'string', 'max:255'],
            'detail_address' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:3'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.item_image' => ['nullable', 'image', 'max:2048'],
            'items.*.remove' => ['nullable', 'boolean'],
        ]);

        $itemsMap = $requestList->items()->get()->keyBy('id');
        $remainingItems = collect($validated['items'])->reject(fn ($item) => !empty($item['remove']));

        if ($remainingItems->isEmpty()) {
            return back()->withErrors(['items' => '至少需保留一項商品'])->withInput();
        }

        if ($remainingItems->count() > 3) {
            return back()->withErrors(['items' => '商品最多只能保留 3 項'])->withInput();
        }

        $firstItemName = $remainingItems->first()['item_name'] ?? '未命名商品';

        $requestList->update([
            'title' => $validated['store_name'] ?: $firstItemName,
            'country' => $validated['country'],
            'deadline' => $validated['deadline'],
            'detail_address' => $validated['detail_address'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        foreach ($validated['items'] as $index => $itemData) {
            $itemId = isset($itemData['id']) ? (int) $itemData['id'] : null;
            $item = $itemId ? $itemsMap->get($itemId) : null;

            if (!empty($itemData['remove'])) {
                if ($item?->reference_image) {
                    Storage::disk('public')->delete($item->reference_image);
                }

                $item?->delete();
                continue;
            }

            $imagePath = $item?->reference_image;
            if ($request->hasFile("items.$index.item_image")) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file("items.$index.item_image")->store('request-items', 'public');
            }

            if ($item) {
                $item->update([
                    'name' => $itemData['item_name'],
                    'quantity' => $itemData['quantity'],
                    'reference_image' => $imagePath,
                ]);
                continue;
            }

            RequestItem::create([
                'request_list_id' => $requestList->id,
                'name' => $itemData['item_name'],
                'quantity' => $itemData['quantity'],
                'reference_image' => $imagePath,
                'reference_url' => null,
                'expected_price' => null,
                'specification' => null,
            ]);
        }

        return redirect()->route('dashboard')->with('status', '請購清單更新成功');
    }

    public function submit(RequestList $requestList)
    {
        abort_unless($requestList->user_id === Auth::id(), 403);

        if ($requestList->status !== 'editing') {
            return redirect()->route('dashboard')->with('status', '僅編輯中的請購清單可送出');
        }

        if ($requestList->items()->count() < 1) {
            return redirect()->route('dashboard')->with('status', '請至少保留 1 項商品後再送出');
        }

        $requestList->update(['status' => 'pending']);

        return redirect()->route('dashboard')->with('status', '請購清單已送出，等待代購人接單');
    }

    public function destroy(RequestList $requestList)
    {
        abort_unless($requestList->user_id === Auth::id(), 403);

        if ($requestList->status !== 'editing') {
            return redirect()->route('dashboard')->with('status', '僅編輯中的請購清單可刪除');
        }

        foreach ($requestList->items as $item) {
            if ($item->reference_image) {
                Storage::disk('public')->delete($item->reference_image);
            }
        }

        $requestList->delete();

        return redirect()->route('dashboard')->with('status', '請購清單已刪除');
    }


    public function image(RequestItem $requestItem)
    {
        // 只要登入即可存取圖片
        if (!auth()->check()) {
            abort(403);
        }

        if (! $requestItem->reference_image || ! Storage::disk('public')->exists($requestItem->reference_image)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($requestItem->reference_image));
    }
            public function submitAgentQuote(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:request_lists,id',
            'agent_quote_total' => 'required|numeric|min:0.01',
            'time' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:request_items,id',
            'items.*.agent_quote' => 'required|numeric|min:0'
        ]);

        $requestList = RequestList::findOrFail($validated['id']);

        if ($requestList->people && $requestList->people != auth()->id()) {
            return response()->json(['message' => '這張需求單已被其他代購承接！'], 422);
        }

        $requestList->load('items');
        $requestItemsById = $requestList->items->keyBy('id');

        if (count($validated['items']) !== $requestItemsById->count()) {
            return response()->json(['message' => '請針對需求單中的每一項商品都填寫報價。'], 422);
        }

        $submittedIds = collect($validated['items'])->pluck('id')->map(fn ($id) => (int) $id);
        if ($submittedIds->unique()->count() !== $submittedIds->count()) {
            return response()->json(['message' => '商品報價資料重複，請重新送出。'], 422);
        }

        $calculatedTotal = 0.0;
        $resolvedItems = [];

        foreach ($validated['items'] as $itemData) {
            $itemId = (int) $itemData['id'];
            $requestItem = $requestItemsById->get($itemId);

            if (! $requestItem) {
                return response()->json(['message' => '含有不屬於此需求單的商品，請重新整理後再送出。'], 422);
            }

            $unitPrice = (float) $itemData['agent_quote'];
            $quantity = max(0, (int) $requestItem->quantity);
            $calculatedTotal += $unitPrice * $quantity;

            $resolvedItems[] = [
                'model' => $requestItem,
                'unit_price' => $unitPrice,
            ];
        }

        $calculatedTotal = round($calculatedTotal, 2);
        $submittedTotal = round((float) $validated['agent_quote_total'], 2);

        if (abs($submittedTotal - $calculatedTotal) > 0.01) {
            return response()->json([
                'message' => '報價總額驗證失敗，請確認「單價 × 需求數量」後重新送出。',
                'expected_total' => $calculatedTotal,
            ], 422);
        }

        $requestList->update([
            'agent_quote_total' => $calculatedTotal,
            'time' => $validated['time'],
            'people' => auth()->id(),
            'status' => 'offered',
        ]);

        foreach ($resolvedItems as $item) {
            $item['model']->update([
                'expected_price' => $item['unit_price'],
                'specification' => $validated['time'],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => '報價成功，已為您承接此單！'
        ]);
    }
   
        
}