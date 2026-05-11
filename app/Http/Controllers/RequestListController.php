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
            'title' => ['required', 'string', 'max:255'],
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

        $user = Auth::user();
       

        $requestList = RequestList::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'store_name' => $validated['store_name'] ?? null,
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
            'title' => ['required', 'string', 'max:255'],
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

     

        $requestList->update([
            'title' => $validated['title'],
            'store_name' => $validated['store_name'] ?? null,
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
    public function complete(RequestList $requestList)
    {
        abort_unless($requestList->user_id === Auth::id(), 403);

        if (in_array($requestList->status, ['arrivaled', 'shipped'], true)) {
            return redirect()->route('dashboard')->with('status', '此請購清單已是結案狀態。');
        }

        $requestList->update(['status' => 'arrivaled']);

        return redirect()->route('dashboard', ['section' => 'request-lists'])->with('status', '請購清單已標記完成，已移至歷史紀錄。');
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
            
   
        
}