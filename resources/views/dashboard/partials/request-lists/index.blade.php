@push('styles')
            <style>
                /* 自定義滾輪樣式 */
                .custom-scrollbar::-webkit-scrollbar {
                    width: 6px; /* 滾輪寬度 */
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    background: #f1f5f9; /* 滾輪軌道背景色 (slate-100) */
                    border-radius: 10px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: #cbd5e1; /* 滾輪本體顏色 (slate-300) */
                    border-radius: 10px;
                    transition: background 0.3s;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background: #94a3b8; /* 移上去變深色 (slate-400) */
                }

                /* 針對 Firefox 的設定 */
                .custom-scrollbar {
                    scrollbar-width: thin;
                    scrollbar-color: #cbd5e1 #f1f5f9;
                }
            </style>
            @endpush
    <div class="flex justify-between items-center mb-6">

                            <h3 class="text-lg font-bold text-gray-800">目前請託單</h3>

                            <div class="flex items-center gap-4">                        

                                <!-- 搜尋框 -->

                                <form method="GET" action="{{ route('dashboard') }}" class="relative w-full md:w-80">
    <input type="hidden" name="section" value="request-lists">

    <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-600 transition">
        <i class="bi bi-search"></i>
    </button>

    <input 
        type="search" 
        name="request_search" 
        placeholder="搜尋標題、描述、狀態..." 
        value="{{ request('request_search') }}"
        class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 shadow-sm transition outline-none"
    >
</form>

                                   <a href="{{ route('request-list.create') }}" class="text-sm text-green-600 hover:underline">+ 建立請購清單</a>

                            </div>

                        </div>



                        <!-- 搜尋結果提示 -->
                        @if(request('request_search'))
                            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm">
                                🔍 搜尋「{{ request('request_search') }}」找到 {{ $requestLists->total() ?? 0 }} 筆清單
                                <a href="{{ route('dashboard') }}" class="text-green-600 hover:underline ml-2">清除搜尋</a>
                            </div>
                        @endif

                
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="text-gray-400 text-sm border-b">
                                        <th class="pb-3 font-medium">商品</th>
                                        <th class="pb-3 font-medium">國家</th>
                                        <th class="pb-3 font-medium">截止日</th>
                                        <th class="pb-3 font-medium">狀態</th>
                                        <th class="pb-3 font-medium">注意事項</th>
                                        <th class="pb-3 font-medium text-right">操作</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y">
                                    @forelse($requestLists ?? [] as $requestList)
                                        @php

                                            $items = $requestList->items ?? collect();

                                            $formatItemLabel = function ($item) {
                                                $name = $item->name ?? '未命名商品';
                                                $qty = (int) ($item->quantity ?? 1);

                                                return $name . '×' . $qty;
                                            };

                                            $firstItem = $items->isNotEmpty()
                                                ? $formatItemLabel($items->first())
                                                : $requestList->title;

                                            $extraItems = $items->slice(1);

                                            $countryLabel = [

                                                'jp' => '日本',

                                                'kr' => '韓國',

                                                'us' => '美國',

                                                'gb' => '英國',

                                            ][$requestList->country] ?? $requestList->country;

                                            $statusLabel = [

                                                'editing' => '編輯中',
                                                'pending' => '等待報價',

                                                'offered' => '代購人已報價',

                                                'matched' => '已確認代購人',

                                                'wait-for-ship' => '等待出貨',

                                                'shipped' => '商品已出貨',

                                                'arrivaled' => '商品已到貨',

                                                'expired' => '已過期',

                                            ][$requestList->status] ?? $requestList->status;

                                            $statusClass = [

                                                'editing' => 'bg-slate-100 text-slate-700',
                                                'pending' => 'bg-yellow-100 text-yellow-700',

                                                'offered' => 'bg-blue-100 text-blue-700',

                                                'matched' => 'bg-green-100 text-green-700',

                                                'wait-for-ship' => 'bg-cyan-100 text-cyan-700',

                                                'shipped' => 'bg-indigo-100 text-indigo-700',

                                                'arrivaled' => 'bg-emerald-100 text-emerald-700',

                                                'expired' => 'bg-rose-100 text-rose-700',

                                            ][$requestList->status] ?? 'bg-gray-100 text-gray-700';

                                        @endphp

                                        <tr class="text-sm align-top">
                                            <td class="py-4 font-medium text-gray-800">
                                                @if($extraItems->isNotEmpty())
                                                    <details class="group">
                                                        <summary class="cursor-pointer select-none hover:text-blue-600">
                                                            {{ $firstItem }}
                                                            <span class="text-xs text-gray-400">（另有 {{ $extraItems->count() }} 項）</span>
                                                        </summary>
                                                        <ul class="mt-2 ml-4 list-disc text-gray-500 text-xs space-y-1">
                                                            @foreach($extraItems as $item)
                                                                <li>{{ $formatItemLabel($item) }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </details>
                                                @else
                                                    {{ $firstItem }}
                                                @endif
                                            </td>

                                            <td class="py-4 text-gray-500">{{ $countryLabel }}</td>
                                            <td class="py-4 text-gray-800">
                                                @if(in_array($requestList->status, ['pending', 'offered', 'matched'], true))
                                                    <button type="button" class="text-blue-600 hover:underline cursor-pointer font-medium" onclick="openRequestCountdownModal({{ $requestList->id }})" title="點擊查看截止倒數">{{ optional($requestList->deadline)->format('Y-m-d') ?? '-' }}</button>
                                                @else
                                                    {{ optional($requestList->deadline)->format('Y-m-d') ?? '-' }}
                                                @endif
                                            </td>
                                            <td class="py-4">
                                               <span class="px-2 py-1 rounded-full text-[10px] {{ $statusClass }}">{{ $statusLabel }}</span>
                                            </td>

                                            <td class="py-4">
                                                @php
                                                    $noticeMap = [
                                                        'editing' => ['text' => '清單送出後將不能修改與刪除,請先確認內容后再按「送出」', 'class' => 'bg-slate-100 text-slate-700'],
                                                        'pending' => ['text' => '等待代購人報價中,請留意通知中心', 'class' => 'bg-yellow-50 text-yellow-700'],
                                                        'offered' => ['text' => '請至通知中心確認代購人', 'class' => 'bg-blue-50 text-blue-700'],
                                                        'matched' => ['text' => '已確認代購人，可檢視、聊天與完成訂單', 'class' => 'bg-green-50 text-green-700'],
                                                        'wait-for-ship' => ['text' => '已結帳等待代購人出貨', 'class' => 'bg-cyan-50 text-cyan-700'],
                                                        'expired' => ['text' => '此請購單已過期', 'class' => 'bg-rose-50 text-rose-700'],
                                                    ];
                                                    $notice = $noticeMap[$requestList->status] ?? null;
                                                @endphp

                                                @if($notice)
                                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-[11px] font-medium {{ $notice['class'] }}">
                                                        {{ $notice['text'] }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-300">—</span>
                                                @endif
                                            </td>

                                            <td class="py-4 text-right">
                                                @php
                                                    $acceptedOffer = $requestList->offers->firstWhere('status', 'accepted');
                                                    $activeOffer = $acceptedOffer ?? $requestList->offers->first();
                                                    $latestActiveQuoteUserId = $requestList->quotes
                                                        ->whereIn('status', ['pending', 'accepted'])
                                                        ->sortByDesc('created_at')
                                                        ->first()?->user_id;
                                                    $chatPartnerId = $requestList->people ?: $latestActiveQuoteUserId;
                                                @endphp

                                                <div class="inline-flex items-center gap-3">
                                                    @if($requestList->status === 'arrivaled')
                                                        {{-- 商品已到貨：完成按鈕可按 --}}
                                                        <button type="button" class="inline-flex items-center rounded-lg bg-blue-500 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-600" onclick="openRequestDetailModal({{ $requestList->id }})">檢視</button>
                                                        <button type="button" class="inline-flex items-center rounded-lg bg-green-400 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-green-500" onclick="openRequestChatModal({{ $requestList->id }}, {{ $requestList->people ?? 'null' }})">聊天</button>
                                                        <form method="POST" action="{{ route('request-list.complete', $requestList) }}" onsubmit="return confirm('確認商品已收到，完成此訂單？');">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-500 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-600">完成</button>
                                                        </form>
                                                    @elseif(in_array($requestList->status, ['matched', 'wait-for-ship', 'shipped'], true))
                                                        {{-- 已確認/等待出貨/已出貨：完成按鈕禁用（尚未到貨）--}}
                                                        <button type="button" class="inline-flex items-center rounded-lg bg-blue-500 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-600" onclick="openRequestDetailModal({{ $requestList->id }})">檢視</button>
                                                        <button type="button" class="inline-flex items-center rounded-lg bg-green-400 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-green-500" onclick="openRequestChatModal({{ $requestList->id }}, {{ $requestList->people ?? 'null' }})">聊天</button>
                                                        <button type="button" class="inline-flex items-center rounded-lg bg-gray-300 px-4 py-2 text-xs font-semibold text-white cursor-not-allowed" disabled title="商品尚未到貨，無法完成">完成</button>
                                                    @elseif($requestList->status === 'editing')
                                                        <button type="button" class="text-blue-500 hover:underline" onclick="openEditModal({{ $requestList->id }})">編輯</button>
                        
                                                        <form method="POST" action="{{ route('request-list.destroy', $requestList) }}" onsubmit="return confirm('確定要刪除此請購清單嗎？');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-500 hover:underline">刪除</button>
                                                        </form>

                                                        <form method="POST" action="{{ route('request-list.submit', $requestList) }}" onsubmit="return confirm('送出後清單將無法修改與刪除,確定送出嗎？');">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="text-green-600 hover:underline">送出</button>
                                                        </form>
                                                    @elseif(in_array($requestList->status, ['pending', 'offered'], true))
                                                        {{-- 等待報價/已報價：完成按鈕顯示但禁用 --}}
                                                        <button type="button" class="inline-flex items-center rounded-lg bg-blue-500 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-600" onclick="openRequestDetailModal({{ $requestList->id }})">檢視</button>
                                                        <button type="button" class="inline-flex items-center rounded-lg bg-gray-300 px-4 py-2 text-xs font-semibold text-white cursor-not-allowed" disabled title="尚未接受報價，無法聊天">聊天</button>
                                                        <button type="button" class="inline-flex items-center rounded-lg bg-gray-300 px-4 py-2 text-xs font-semibold text-white cursor-not-allowed" disabled title="商品尚未到貨，無法完成">完成</button>
                                                        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-amber-200 bg-amber-100 text-amber-600 shadow-sm transition hover:bg-amber-200" title="通知中心" aria-label="通知中心" onclick="openRequestNoticeModal({{ $requestList->id }})">
                                                            🔔
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="6" class="py-6 text-center text-gray-400">
                                                @if(request('request_search'))
                                                    沒有找到「{{ request('request_search') }}」相關的請購清單
                                                @else
                                                    目前尚未建立請購清單
                                                @endif
                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>
                            </table>
                        </div>
                    </div>

                        @foreach($requestLists ?? [] as $requestList)
                             @if(in_array($requestList->status, ['pending', 'offered', 'matched'], true))
                                @php
                                    $modalCountryLabel = [
                                        'jp' => '日本',
                                        'kr' => '韓國',
                                        'us' => '美國',
                                        'gb' => '英國',
                                    ][$requestList->country] ?? $requestList->country;
                                    $acceptedOffer = $requestList->offers->firstWhere('status', 'accepted');
                                    $activeOffer = $acceptedOffer ?? $requestList->offers->first();
                                    $activeAgent = optional($activeOffer)->agent;
                                    $assignedAgentUser = (in_array($requestList->status, ['offered', 'matched'], true) && !empty($requestList->people))
                                        ? \App\Models\User::find($requestList->people)
                                        : null;
                                    $displayAgent = $assignedAgentUser ?: $activeAgent;
                                    $agentAvatar = $displayAgent && $displayAgent->avatar
                                        ? asset('storage/' . $displayAgent->avatar)
                                        : ($displayAgent
                                            ? 'https://ui-avatars.com/api/?name=' . urlencode($displayAgent->name) . '&background=2563eb&color=fff&size=128'
                                            : null);

                                    $deadlineDisplayDate = optional($requestList->deadline)->format('Y-m-d');
                                    $countdownEndAt = optional($requestList->deadline)->format('Y-m-d') ? optional($requestList->deadline)->format('Y-m-d') . ' 23:59:00' : null;

                                    $isOffered = $requestList->status === 'offered';
                                    $isMatched = $requestList->status === 'matched';
                                    $modalHeading = $isMatched ? '已確認代購人的請託單' : ($isOffered ? '代購人已報價的請託單' : '等待報價中的請託單');
                                    $modalSubHeading = '可查看商品明細與目前接單狀況。';

                                @endphp

                                <div id="request-detail-modal-{{ $requestList->id }}" class="request-detail-modal hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-4" onclick="handleRequestDetailBackdrop(event, {{ $requestList->id }})">
                                    <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                                        <div class="flex items-start justify-between gap-4 border-b border-slate-200 bg-gradient-to-r from-blue-600 to-cyan-500 px-5 py-4 text-white">
                                            <div>
                                                 <p class="text-sm font-medium text-blue-100">{{ $modalHeading }}</p>
                                                <h4 class="mt-1 text-2xl font-bold">{{ $requestList->title }}</h4>
                                               <p class="mt-2 text-sm text-blue-50">{{ $modalSubHeading }}</p>
                                            </div>
                                            <button type="button" class="rounded-full bg-white/15 p-2 text-white transition hover:bg-white/25" onclick="closeRequestDetailModal({{ $requestList->id }})" aria-label="關閉檢視視窗">✕</button>
                                        </div>

                                        <div class="max-h-[72vh] overflow-y-auto px-5 py-5">
                                            <div class="grid gap-4 lg:grid-cols-[minmax(0,1.6fr)_minmax(220px,0.9fr)]">
                                                <div class="space-y-4">
                                                    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                        <h5 class="text-base font-bold text-slate-800">商品明細</h5>
                                                        <div class="mt-3 space-y-3">
                                                            @forelse($requestList->items as $item)
                                                                <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:flex-row sm:items-center">
                                                                    <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-xl bg-slate-100">
                                                                        @if($item->reference_image)
                                                                              <img src="{{ route('request-item.image', ['requestList' => $requestList->id, 'requestItem' => $item->id, 'v' => $item->updated_at?->timestamp ?? now()->timestamp]) }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                                                        @else
                                                                            <span class="text-xs text-slate-400">無商品圖片</span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                                                            <div>
                                                                                <p class="text-base font-semibold text-slate-800">{{ $item->name }}</p>
                                                                                <p class="mt-1 text-sm text-slate-500">數量：{{ $item->quantity }}</p>
                                                                            </div>
                                                                            @if($item->reference_url)
                                                                                <a href="{{ $item->reference_url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">參考連結</a>
                                                                            @endif
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center text-sm text-slate-400">目前沒有商品資料。</div>
                                                            @endforelse
                                                        </div>
                                                    </section>

                                                    <section class="grid gap-3 md:grid-cols-2">
                                                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                                            <h5 class="text-base font-bold text-slate-800">商家與購買資訊</h5>
                                                            <dl class="mt-3 space-y-3 text-sm">
                                                                <div class="flex items-start justify-between gap-4">
                                                                    <dt class="text-slate-500">國家</dt>
                                                                    <dd class="text-right font-medium text-slate-800">{{ $modalCountryLabel }}</dd>
                                                                </div>
                                                                <div class="flex items-start justify-between gap-4">
                                                                    <dt class="text-slate-500">店家</dt>
                                                                    <dd class="text-right font-medium text-slate-800">{{ $requestList->store_name ?: '未提供' }}</dd>
                                                                </div>
                                                                <div>
                                                                    <dt class="text-slate-500">店家詳細地址</dt>
                                                                    <dd class="mt-1 rounded-xl bg-slate-50 px-3 py-3 leading-6 text-slate-700">{{ $requestList->detail_address ?: '未提供詳細地址' }}</dd>
                                                                </div>
                                                                <div class="flex items-start justify-between gap-4">
                                                                    <dt class="text-slate-500">商品截止日</dt>
                                                                    <dd class="text-right font-medium text-slate-800">{{ optional($requestList->deadline)->format('Y-m-d') ?? '未提供' }}</dd>
                                                                </div>
                                                            </dl>
                                                        </div>

                                                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                                            <h5 class="text-base font-bold text-slate-800">備註</h5>
                                                            <dl class="mt-3 space-y-3 text-sm">
                                                                <div>
                                                                    <dt class="text-slate-500">內容</dt>
                                                                    <dd class="mt-1 rounded-xl bg-slate-50 px-3 py-3 leading-6 text-slate-700">{{ $requestList->note ?: '目前沒有備註。' }}</dd>
                                                                </div>
                                                            </dl>
                                                        </div>
                                                    </section>
                                                </div>

                                                <aside class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                    <h5 class="text-base font-bold text-slate-800">目前接單狀況</h5>
                                                    @php
                                                        $pricedOffers = $requestList->offers->filter(function ($offer) {
                                                            return !is_null($offer->offered_price);
                                                        });
                                                        $pricedQuotes = $requestList->quotes->filter(function ($quote) {
                                                            return !is_null($quote->price);
                                                        });
                                                        $hasQuotedItems = $pricedOffers->isNotEmpty() || $pricedQuotes->isNotEmpty();
                                                    @endphp

                                                    @php
                                                        $pricedQuotes = $pricedQuotes->filter(function ($quote) {
                                                            return $quote->status !== 'rejected';
                                                        });
                                                    @endphp

                                                    @if($pricedQuotes->isNotEmpty())
                                                        <div class="mt-4 space-y-4 rounded-2xl bg-white p-4 shadow-sm">
                                                            @foreach($pricedQuotes as $quote)
                                                                @php
                                                                    // 獲取該報價的商品單價（如果表存在）
                                                                    $quoteItemPrices = method_exists($quote, 'quoteItems') ? $quote->quoteItems->keyBy('request_item_id') : collect();
                                                                @endphp
                                                                <div class="rounded-2xl border border-slate-200 p-4 space-y-3">
                                                                    <p class="text-xs font-semibold text-slate-800">代購人：{{ $quote->user->name ?? '未知代購人' }}</p>

                                                                    <div class="space-y-2 text-xs text-slate-500">
                                                                        @foreach($requestList->items as $item)
                                                                            @php
                                                                                $quotedPrice = $quoteItemPrices->get($item->id)?->unit_price ?? $item->expected_price ?? 0;
                                                                            @endphp
                                                                            <div class="flex items-center justify-between gap-3">
                                                                                <span class="truncate">{{ $item->name }}</span>
                                                                                <span class="shrink-0 font-medium text-slate-700">NT$ {{ number_format($quotedPrice, 0) }}</span>
                                                                            </div>                             
                                                                        @endforeach 
                                                                    </div>
                                                                        
                                                                    <p class="text-xs text-slate-500">預計代購日期：{{ $quote->estimated_date ?? '未提供' }}</p>
                                                                    <p class="text-xs text-slate-500">報價備註：{{ $quote->comment ?? '未提供' }}</p>
                                                                    <div>                                                         
                                                                        <span class="text-blue-600 font-extrabold text-xs">
                                                                                總計：NT$ {{ number_format($quote->price) }}
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                       @php
                                                                            $quoteStatus = $quote->status ?? 'pending';
                                                                            $statusLabel = [
                                                                                'pending' => '待確認',
                                                                                'accepted' => '已接受',
                                                                            ][$quoteStatus] ?? $quoteStatus;
                                                                            $statusClass = [
                                                                                'pending' => 'bg-amber-100 text-amber-700',
                                                                                'accepted' => 'bg-emerald-100 text-emerald-700',
                                                                            ][$quoteStatus] ?? 'bg-slate-100 text-slate-700';
                                                                        @endphp
                                                                        <span class="inline-flex rounded-full px-3 py-1 text-[10px] {{ $statusClass }}">{{ $statusLabel }}</span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif($pricedOffers->isNotEmpty())
                                                        <div class="mt-4 space-y-4 rounded-2xl bg-white p-4 shadow-sm">
                                                            @foreach($pricedOffers as $offer)
                                                                @php
                                                                    $totalQty = max(1, $requestList->items->sum('quantity'));
                                                                    $unitPrice = round((float) $offer->offered_price / $totalQty, 0);
                                                                @endphp
                                                                <div class="rounded-2xl border border-slate-200 p-4">
                                                                    <p class="text-xs font-semibold text-slate-800">代購人：{{ $offer->agent->name ?? '未知代購人' }}</p>
                                                                    <div class="mt-2 text-sm text-slate-500 space-y-2">
                                                                        @foreach($requestList->items as $item)
                                                                            <p>{{ $item->name }}單價：NT$ {{ number_format($item->expected_price ?? $unitPrice, 0) }}</p>
                                                                        @endforeach
                                                                        <p>預計代購日期：未提供</p>
                                                                        <p>報價備註：未提供</p>
                                                                        <p class="font-medium text-slate-700">待確認</p>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif($displayAgent)
                                                        <div class="mt-4 rounded-2xl bg-white p-4 text-center shadow-sm">
                                                            <div class="mx-auto flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-4 border-blue-100 bg-slate-100">
                                                                <img src="{{ $agentAvatar }}" alt="{{ $displayAgent->name }}" class="h-full w-full object-cover">
                                                            </div>
                                                            <p class="mt-3 text-base font-semibold text-slate-800">{{ $displayAgent->name }}</p>
                                                            <p class="mt-1 text-sm text-slate-500">{{ $requestList->status === 'offered' ? '此代購人已下單並提供商品單價' : ($acceptedOffer ? '已確認接單的代購人' : '已有代購人提出接單意願') }}</p>

                                                            @if(in_array($requestList->status, ['offered', 'matched'], true))
                                                                @php
                                                                    $specification = $requestList->time ?: collect($requestList->items ?? [])->pluck('specification')->filter()->unique()->implode('、');
                                                                @endphp
                                                                <p class="mt-2 rounded-xl bg-blue-50 px-3 py-2 text-sm text-blue-700">
                                                                    可代購時段：{{ $specification ?: '未提供' }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center">
                                                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-2xl">🕒</div>
                                                            <p class="mt-4 text-sm font-medium text-slate-700">目前尚未有代購人接單</p>
                                                            <p class="mt-2 text-xs leading-5 text-slate-400">當有代購人接單或提出報價時，這裡會顯示對方的名字與各商品之單價。</p>
                                                        </div>
                                                    @endif

                                                    @if($requestList->status === 'matched')
                                                        <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                                            <h6 class="text-sm font-bold text-emerald-700">物流狀況</h6>
                                                            <p class="mt-2 text-sm text-emerald-800">
                                                                {{ $requestList->logistics_status ?? '代購人已確認接單，待更新物流進度。' }}
                                                            </p>
                                                        </div>
                                                    @endif
                                                </aside>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                              <div id="request-countdown-modal-{{ $requestList->id }}" class="request-countdown-modal hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-4" onclick="handleRequestCountdownBackdrop(event, {{ $requestList->id }})">
                                    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
                                        <div class="flex items-start justify-between gap-3 border-b border-orange-100 bg-orange-500 px-5 py-4 text-white">
                                            <div>
                                                <p class="text-sm font-medium text-orange-100">請購清單截止倒數</p>
                                                <h4 class="mt-1 text-xl font-bold">{{ $requestList->title }}</h4>
                                            </div>
                                            <button type="button" class="rounded-full bg-white/20 p-2 text-white transition hover:bg-white/30" onclick="closeRequestCountdownModal({{ $requestList->id }})" aria-label="關閉倒數視窗">✕</button>
                                        </div>
                                        <div class="px-5 py-6 text-center">
                                            <p class="text-sm text-slate-500">距離截止尚餘</p>
                                            <p
                                                id="request-countdown-text-{{ $requestList->id }}"
                                                class="mt-2 text-3xl font-extrabold tracking-wide text-orange-600"
                                                data-end-at="{{ $countdownEndAt }}"
                                            >
                                                --:--:--
                                            </p>
                                            <p class="mt-4 rounded-xl bg-orange-50 px-4 py-3 text-sm text-orange-700">
                                                此清單將在截止日 {{ $deadlineDisplayDate ?? '未提供' }} 隔天 00:00 移除
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @php
                                    $quotedAgents = $requestList->quotes
                                        ->whereIn('status', ['pending', 'accepted'])
                                        ->map(fn ($quote) => $quote->user)
                                        ->filter()
                                        ->unique('id')
                                        ->values();
                                    $chatAgents = $quotedAgents;
                                @endphp

                                @if($chatAgents->isNotEmpty())
                                    <div id="request-chat-modal-{{ $requestList->id }}" class="request-chat-modal hidden fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/60 px-4 py-6" onclick="handleRequestChatBackdrop(event, {{ $requestList->id }})">
                                        <div class="w-full max-w-5xl rounded-2xl bg-white shadow-2xl overflow-hidden">
                                            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                                                <div>
                                                    <h4 class="text-lg font-bold text-slate-800">請購單聊天室</h4>
                                                    <p class="text-sm text-slate-500">{{ $requestList->title }}</p>
                                                </div>
                                                <button type="button" class="text-slate-500 text-2xl leading-none hover:text-slate-700" onclick="closeRequestChatModal({{ $requestList->id }})" aria-label="關閉聊天室">✕</button>
                                            </div>

                                            <div class="grid lg:grid-cols-[280px_minmax(0,1fr)] request-chat-grid">
                                                <aside class="request-chat-sidebar border-r border-slate-200 bg-slate-50">
                                                    <div class="max-h-[70vh] overflow-y-auto">
                                                        @foreach($chatAgents as $chatAgent)
                                                            @php
                                                                $agentQuote = $requestList->quotes->firstWhere('user_id', $chatAgent->id);
                                                                $quoteItemPrices = method_exists($agentQuote, 'quoteItems')
                                                                    ? $agentQuote->quoteItems->keyBy('request_item_id')
                                                                    : collect();
                                                                $agentUnitPrices = $requestList->items->map(function ($item) use ($quoteItemPrices) {
                                                                    $unitPrice = $quoteItemPrices->get($item->id)?->unit_price;

                                                                    if ($unitPrice === null) {
                                                                        return null;
                                                                    }

                                                                    return number_format((float) $unitPrice, 0);
                                                                })->filter()->values();

                                                                $activeClass = $loop->first
                                                                    ? 'bg-emerald-100 text-emerald-700 border-l-4 border-emerald-500'
                                                                    : 'text-slate-600 hover:bg-slate-100';
                                                            @endphp
                                                            <button
                                                                type="button"
                                                                class="request-chat-agent-tab w-full px-4 py-3 text-left text-sm transition {{ $activeClass }}"
                                                                data-request-list-id="{{ $requestList->id }}"
                                                                data-agent-id="{{ $chatAgent->id }}"
                                                                onclick="switchRequestChatAgent({{ $requestList->id }}, {{ $chatAgent->id }})"
                                                            >
                                                                <p class="font-semibold">{{ $chatAgent->name }}</p>
                                                                <p class="mt-1 text-xs text-slate-500">
                                                                    單價：
                                                                    <span class="font-semibold text-emerald-600">
                                                                        NT${{ $agentUnitPrices->isNotEmpty() ? $agentUnitPrices->implode('/NT$') : '-' }}
                                                                    </span>
                                                                </p>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </aside>

                                                <div class="bg-white">
                                            @foreach($chatAgents as $idx => $chatAgent)
                                                @php
                                                    $agentQuote = $requestList->quotes->firstWhere('user_id', $chatAgent->id);
                                                    $quoteItemPrices = method_exists($agentQuote, 'quoteItems')
                                                        ? $agentQuote->quoteItems->keyBy('request_item_id')
                                                        : collect();
                                                    $agentUnitPrices = $requestList->items->map(function ($item) use ($quoteItemPrices) {
                                                        $unitPrice = $quoteItemPrices->get($item->id)?->unit_price;

                                                        if ($unitPrice === null) {
                                                            return null;
                                                        }

                                                        return number_format((float) $unitPrice, 0);
                                                    })->filter()->values();
                                                    $chatMessages = \App\Models\Message::query()
                                                        ->where('request_list_id', $requestList->id)
                                                        ->where(function ($query) use ($requestList, $chatAgent) {
                                                            $query->where(function ($inner) use ($requestList, $chatAgent) {
                                                                $inner->where('sender_id', $requestList->user_id)
                                                                    ->where('receiver_id', $chatAgent->id);
                                                            })->orWhere(function ($inner) use ($requestList, $chatAgent) {
                                                                $inner->where('sender_id', $chatAgent->id)
                                                                    ->where('receiver_id', $requestList->user_id);
                                                            });
                                                        })
                                                        ->with(['sender:id,name'])
                                                        ->orderBy('created_at')
                                                        ->get();
                                                @endphp
                                                <div class="request-chat-agent-panel {{ $idx === 0 ? '' : 'hidden' }}" data-request-list-id="{{ $requestList->id }}" data-agent-id="{{ $chatAgent->id }}">
                                                    <div class="border-b border-slate-200 bg-slate-50 px-5 py-3 text-sm text-slate-700">
                                                        該代購人填寫之單價：
                                                        <span class="font-bold text-emerald-600">
                                                            NT${{ $agentUnitPrices->isNotEmpty() ? $agentUnitPrices->implode('/NT$') : '-' }}
                                                        </span>
                                                    </div>

                                                    <div id="request-chat-messages-{{ $requestList->id }}-{{ $chatAgent->id }}" class="max-h-[55vh] overflow-y-auto bg-slate-50 px-5 py-4">
                                                        @forelse($chatMessages as $message)
                                                            @php($isMine = (int) $message->sender_id === (int) auth()->id())
                                                            <div class="mb-3 flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                                                <div class="max-w-[75%]">
                                                                    <div class="rounded-xl border px-3 py-2 {{ $isMine ? 'bg-emerald-100 border-emerald-200' : 'bg-white border-slate-200' }}">
                                                                        <p class="text-xs text-slate-500">{{ $message->sender->name ?? '使用者' }}</p>
                                                                        <p class="mt-1 text-sm text-slate-800 break-words">{{ $message->body }}</p>
                                                                    </div>
                                                                    <p class="mt-1 text-xs text-slate-500 {{ $isMine ? 'text-right' : 'text-left' }}">
                                                                        {{ optional($message->created_at)->format('Y-m-d H:i') }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <p class="py-12 text-center text-sm text-slate-400">目前尚無訊息，開始第一句對話吧。</p>
                                                        @endforelse
                                                    </div>

                                                    <form method="POST"
                                                        action="{{ route('request-list.chat.send', $requestList) }}"
                                                        class="request-chat-form flex items-center gap-2 border-t border-slate-200 px-4 py-3"
                                                        data-request-list-id="{{ $requestList->id }}">
                                                        @csrf
                                                        <input type="hidden" name="receiver_id" value="{{ $chatAgent->id }}">
                                                        <input type="text" name="body" class="w-full rounded-full border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="輸入訊息..." maxlength="2000" required>
                                                        <button type="submit" class="rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">送出</button>
                                                    </form>
                                                </div>
                                            @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            @include('dashboard.partials.request-lists._notice_modal', ['requestList' => $requestList])
   
                            @endif

                            @if($requestList->status === 'editing')
                                <div id="edit-modal-{{ $requestList->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
                                    <div class="bg-white w-full max-w-3xl rounded-xl shadow-lg max-h-[90vh] overflow-y-auto">
                                        <div class="flex justify-between items-center border-b px-6 py-4">
                                            <h4 class="text-lg font-bold text-gray-800">編輯請購清單</h4>
                                            <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeEditModal({{ $requestList->id }})">✕</button>
                                        </div>

                                        <form action="{{ route('request-list.update', $requestList) }}" method="POST" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
                                            @csrf
                                            @method('PUT')

                                             <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">請託單標題</label>
                                                <input type="text" name="title" value="{{ $requestList->title }}" class="w-full border-gray-300 rounded-lg" required>
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">國家</label>
                                                    <select name="country" class="w-full border-gray-300 rounded-lg">
                                                        <option value="jp" @selected($requestList->country === 'jp')>日本</option>
                                                        <option value="kr" @selected($requestList->country === 'kr')>韓國</option>
                                                        <option value="us" @selected($requestList->country === 'us')>美國</option>
                                                        <option value="gb" @selected($requestList->country === 'gb')>英國</option>
                                                    </select>

                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">商品截止日</label>
                                                    <input
                                                        type="date"
                                                        name="deadline"
                                                        value="{{ optional($requestList->deadline)->format('Y-m-d') }}"
                                                        min="{{ optional($requestList->created_at)->format('Y-m-d') }}"
                                                        max="{{ optional($requestList->created_at)->addMonth()->format('Y-m-d') }}"
                                                        class="w-full border-gray-300 rounded-lg"
                                                    >
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">店家</label>
                                                    <input type="text" name="store_name" value="{{ $requestList->store_name }}" class="w-full border-gray-300 rounded-lg" placeholder="請輸入店家名稱">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">詳細地址</label>
                                                    <input type="text" name="detail_address" value="{{ $requestList->detail_address }}" class="w-full border-gray-300 rounded-lg" placeholder="請輸入詳細地址">
                                                </div>
                                            </div>


                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">備註</label>
                                                <textarea name="note" class="w-full border-gray-300 rounded-lg" rows="2" placeholder="可填寫代購需求補充、規格、交付注意事項等">{{ $requestList->note }}</textarea>
                                            </div>

                                            <div class="space-y-4 pt-2 edit-items-wrapper" data-request-list-id="{{ $requestList->id }}" data-max-items="3" data-next-index="{{ $requestList->items->count() }}">
                                                <div class="flex items-center justify-between gap-3">
                                                    <h5 class="font-semibold text-gray-800">商品資料</h5>
                                                    <button type="button" class="px-3 py-2 text-sm rounded-lg border border-green-200 text-green-600 hover:bg-green-50 disabled:opacity-50 disabled:cursor-not-allowed edit-add-item-btn" onclick="addEditItem({{ $requestList->id }})">新增商品</button>
                                                </div>
                                                <p class="text-xs text-gray-500 edit-item-limit-hint">最多可保留 3 項商品。</p>

                                                <div class="space-y-3 edit-item-list">
                                                    @foreach($requestList->items->take(3) as $index => $item)
                                                        <div class="border rounded-lg p-3 space-y-2 edit-item-card" data-existing="1">
                                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                            <input type="hidden" name="items[{{ $index }}][remove]" value="0" class="remove-flag">
                                                            <div class="flex items-center justify-between">
                                                                <label class="block text-sm font-medium text-gray-700">商品名稱</label>
                                                                <button type="button" class="text-xs text-red-500 hover:underline" onclick="removeEditItem(this)">刪除此商品</button>
                                                            </div>

                                                            <div>
                                                                <input type="text" name="items[{{ $index }}][item_name]" value="{{ $item->name }}" class="w-full border-gray-300 rounded-lg" required>
                                                            </div>

                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">數量</label>
                                                                <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="1" step="1" class="w-full border-gray-300 rounded-lg" required>
                                                            </div>

                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">商品圖片</label>
                                                                @if($item->reference_image)
                                                                  <img src="{{ route('request-item.image', ['requestList' => $requestList->id, 'requestItem' => $item->id, 'v' => $item->updated_at?->timestamp ?? now()->timestamp]) }}" alt="商品圖片" class="w-24 h-24 object-cover rounded border mb-2 edit-item-image-preview" data-original-src="{{ route('request-item.image', ['requestList' => $requestList->id, 'requestItem' => $item->id, 'v' => $item->updated_at?->timestamp ?? now()->timestamp]) }}">
                                                                    <p class="text-xs text-gray-500 mb-2 edit-item-image-status">未重新上傳會保留原圖片</p>
                                                                @endif
                                                                <input type="file" name="items[{{ $index }}][item_image]" class="w-full border-gray-300 rounded-lg edit-item-image-input" accept="image/*">
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <template id="edit-item-template-{{ $requestList->id }}">
                                                    <div class="border rounded-lg p-3 space-y-2 edit-item-card" data-existing="0">
                                                        <input type="hidden" name="items[__INDEX__][remove]" value="0" class="remove-flag">
                                                        <div class="flex items-center justify-between">
                                                            <label class="block text-sm font-medium text-gray-700">商品名稱</label>
                                                            <button type="button" class="text-xs text-red-500 hover:underline" onclick="removeEditItem(this)">刪除此商品</button>
                                                        </div>

                                                        <div>
                                                            <input type="text" name="items[__INDEX__][item_name]" class="w-full border-gray-300 rounded-lg" placeholder="請輸入商品名稱" required>
                                                        </div>

                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">數量</label>
                                                            <input type="number" name="items[__INDEX__][quantity]" value="1" min="1" step="1" class="w-full border-gray-300 rounded-lg" required>
                                                        </div>

                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">商品圖片</label>
                                                             <input type="file" name="items[__INDEX__][item_image]" class="w-full border-gray-300 rounded-lg edit-item-image-input" accept="image/*">
                                                        </div>
                                                    </div>
                                                </template>

                                            </div>



                                            <div class="flex justify-end gap-2 pt-2">
                                                <button type="button" class="px-4 py-2 rounded-lg border text-gray-600" onclick="closeEditModal({{ $requestList->id }})">取消</button>
                                                <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white">確認</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            @endif

                         @endforeach

@once
    <script>
        function openQuoteDetailModal(quoteId) {
            const modal = document.getElementById(`quote-detail-modal-${quoteId}`);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex', 'items-center');
            document.body.style.overflow = 'hidden';
        }

        function closeQuoteDetailModal(quoteId) {
            const modal = document.getElementById(`quote-detail-modal-${quoteId}`);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex', 'items-center');
            document.body.style.overflow = '';
        }

        function handleQuoteDetailBackdrop(event, quoteId) {
            if (event.target.id === `quote-detail-modal-${quoteId}`) {
                closeQuoteDetailModal(quoteId);
            }
        }
    </script>
@endonce