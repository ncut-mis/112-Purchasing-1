<?php

namespace App\Http\Controllers;

use App\Models\RequestItem;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Favorite;
use App\Models\AgentNotification;
use App\Models\User;

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
            $imageData = null;
            if ($request->hasFile("items.$index.item_image")) {
                $imageData = file_get_contents($request->file("items.$index.item_image")->getRealPath());
            }

            RequestItem::create([
                'request_list_id' => $requestList->id,
                'name' => $item['item_name'],
                'quantity' => $item['quantity'],
                'reference_image' => $imageData,
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
                    $this->deleteStorageFileIfExists($item->reference_image);
                }

                $item?->delete();
                continue;
            }

            $imageData = $item?->reference_image;
            if ($request->hasFile("items.$index.item_image")) {
                $this->deleteStorageFileIfExists($item?->reference_image);
                $imageData = file_get_contents($request->file("items.$index.item_image")->getRealPath());
            }

            if ($item) {
                $item->update([
                    'name' => $itemData['item_name'],
                    'quantity' => $itemData['quantity'],
                    'reference_image' => $imageData,
                ]);
                continue;
            }

            RequestItem::create([
                'request_list_id' => $requestList->id,
                'name' => $itemData['item_name'],
                'quantity' => $itemData['quantity'],
                'reference_image' => $imageData,
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

        // 1. 變更狀態
        $requestList->update(['status' => 'pending']);

        $buyer = Auth::user();
        $targetCountry = $requestList->country; 

        $countryMap = [
            'jp' => '日本',
            'kr' => '韓國',
            'us' => '美國',
        ];
        $searchWord = $countryMap[strtolower($targetCountry)] ?? $targetCountry;

        // 🔍 2. 🔥【徹底破局】不使用任何 Model 關聯！直接用原生 Query Builder 進行雙表 Join
        // 💡 備註：如果你的代購申請表裡串用戶的欄位是 agent_id，請把下面這行的 'user_id' 改成 'agent_id'！
        $allAgents = \DB::table('users')
            ->join('agent_applications', 'users.id', '=', 'agent_applications.user_id') // 👈 這裡如果是 agent_id 請記得改！
            ->where('users.id', '!=', $buyer->id)
            ->where('agent_applications.status', 'approved')
            ->select('users.*')
            ->get();

        // 3. 用 PHP 去篩選雙重引號的國家
        $matchedAgents = $allAgents->filter(function ($agent) use ($searchWord) {
            $rawCountries = $agent->purchasable_countries;
            
            if (empty($rawCountries)) return false;

            $decoded = json_decode($rawCountries, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            return is_array($decoded) && in_array($searchWord, $decoded);
        });

        // 🔔 4. 寫入通知資料表
        foreach ($matchedAgents as $agent) {
            \App\Models\AgentNotification::create([
                'agent_id'        => $agent->id,
                'buyer_id'        => $buyer->id,
                'request_list_id' => $requestList->id,
                'title'           => '推薦請購人',
                'content'         => '發現符合您國家的全新請購清單！',
                'is_read'         => false,
            ]);
        }

        return redirect()->route('dashboard')->with('status', '請購清單已送出，系統已同步推薦給對應國家的代購人！');
    }


    public function complete(RequestList $requestList)
    {
        abort_unless($requestList->user_id === Auth::id(), 403);

        if ($requestList->status !== 'arrivaled') {
            return redirect()->route('dashboard')->with('error', '只有商品已到貨的請購清單才能標記為完成');
        }


        $requestList->update(['status' => 'completed']);

        return redirect()->route('dashboard')->with('status', '請購清單已標記為完成！');
    }

    public function destroy(RequestList $requestList)
    {
        abort_unless($requestList->user_id === Auth::id(), 403);

        $requestList->items()->delete();
        $requestList->delete();

        return redirect()->route('dashboard')->with('status', '請購清單已刪除！');
    }

    public function image(RequestItem $requestItem)
    {
        // 只要登入即可存取圖片
        if (!auth()->check()) {
            abort(403);
        }

        $imageData = $requestItem->reference_image;

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

    public function readNotification($buyer_id, $request_list_id)
    {
        // 1. 安全檢查：確保使用者已登入
        if (!\Illuminate\Support\Facades\Auth::check()) {
            return redirect()->route('login');
        }

        // 2. 將目前登入代購人收到來自該買家的所有未讀通知全部改成已讀
        \App\Models\AgentNotification::where('agent_id', \Illuminate\Support\Facades\Auth::id())
            ->where('buyer_id', $buyer_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // 🎯【關鍵修正】：不要轉址到 /agent/request-list/{id}
        // 必須直接導向「接單大廳 (agent.dashboard)」，並在網址後面帶上 search_buyer_id 參數！
        return redirect()->route('agent.dashboard', ['search_buyer_id' => $buyer_id]);
    }
    
    public function saveSelection(Request $request)
    {
        // 1. 將該代理商所有通知的 is_selected 重置為 false
        AgentNotification::where('agent_id', auth()->id())
            ->update(['is_selected' => false]);

        // 2. 如果有勾選，將勾選的 IDs 更新為 true
        if ($request->has('selected_notifications')) {
            AgentNotification::whereIn('id', $request->selected_notifications)
                ->update(['is_selected' => true]);
        }

        return back()->with('success', '已更新選擇狀態');
    }
    public function selectNotifications(Request $request)
    {
        // 1. 將該代理人的所有通知重置為未選取
        \App\Models\AgentNotification::where('agent_id', auth()->id())
            ->update(['is_selected' => false]);

        // 2. 將選中的 ID 設為 true
        if ($request->has('selected_notifications')) {
            \App\Models\AgentNotification::whereIn('id', $request->selected_notifications)
                ->update(['is_selected' => true]);
        }

        // 3. 改為回傳 JSON，配合前端的 fetch 進行跳轉
        return response()->json(['success' => true]);
    }
   
    public function showBuyerDetails($id)
    {
        // 1. 取得該請託人的資料
        $user = User::findOrFail($id);
        
        // 2. 取得該請託人的所有需求清單
        $requestLists = RequestList::where('user_id', $id)->paginate(10);
        
        // 3. 導向代購大廳，並把資料傳過去 (假設你的大廳視圖是 agent.dashboard)
        return view('agent.dashboard', compact('user', 'requestLists'));
    }
    public function selectBuyers(Request $request)
{
    $buyerIds = $request->input('selected_buyers', []); // 獲取所有勾選的 ID
    
    // 將這些 ID 存入 Session 或資料庫，以便導向到代購大廳時讀取
    session(['selected_buyer_ids' => $buyerIds]);
    
    return response()->json(['success' => true]);
}
    public function clearFilter()
{
    $agentId = auth()->id(); // 確保獲取當前登入的 agent ID

    if ($agentId) {
        \App\Models\AgentNotification::where('agent_id', $agentId)
            ->update([
                'is_selected' => false // 明確將所有相關記錄設為 0
            ]);
    }

    return redirect()->route('agent.dashboard');
}
}
