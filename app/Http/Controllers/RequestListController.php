<?php

namespace App\Http\Controllers;

use App\Models\RequestItem;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RequestListController extends Controller
{
    public function create()
    {
        return view('request-list.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'country' => ['required', 'string', 'max:255'],
            'deadline' => ['required', 'date'],
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
            'status' => 'pending',
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

        $validated = $request->validate([
            'country' => ['required', 'string', 'max:255'],
            'deadline' => ['required', 'date'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'detail_address' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:3'],
            'items.*.id' => ['required', 'integer'],
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

        $firstItemName = $remainingItems->first()['item_name'] ?? '未命名商品';

        $requestList->update([
            'title' => $validated['store_name'] ?: $firstItemName,
            'country' => $validated['country'],
            'deadline' => $validated['deadline'],
            'detail_address' => $validated['detail_address'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        foreach ($validated['items'] as $index => $itemData) {
            $item = $itemsMap->get((int) $itemData['id']);
            if (! $item) {
                continue;
            }

            if (!empty($itemData['remove'])) {
                if ($item->reference_image) {
                    Storage::disk('public')->delete($item->reference_image);
                }
                $item->delete();
                continue;
            }

            $imagePath = $item->reference_image;
            if ($request->hasFile("items.$index.item_image")) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file("items.$index.item_image")->store('request-items', 'public');
            }

            $item->update([
                'name' => $itemData['item_name'],
                'quantity' => $itemData['quantity'],
                'reference_image' => $imagePath,
            ]);
        }

        return redirect()->route('dashboard')->with('status', '請購清單更新成功');
    }

    public function destroy(RequestList $requestList)
    {
        abort_unless($requestList->user_id === Auth::id(), 403);

        if ($requestList->status !== 'pending') {
            return redirect()->route('dashboard')->with('status', '僅等待接單的請購清單可刪除');
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
}