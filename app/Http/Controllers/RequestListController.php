<?php

namespace App\Http\Controllers;

use App\Models\RequestItem;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'address_detail' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:3'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.item_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $firstItemName = $validated['items'][0]['item_name'] ?? '未命名商品';

        $requestList = RequestList::create([
            'user_id' => Auth::id(),
            'title' => $validated['store_name'] ?: $firstItemName,
            'country' => $validated['country'],
            'city' => null,
            'deadline' => $validated['deadline'],
            'budget_total' => null,
            'currency' => 'TWD',
            'status' => 'pending',
            'note' => $validated['address_detail'] ?? null,
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
}