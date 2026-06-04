@php
    use Illuminate\Support\Facades\Auth;
    $userId = Auth::id();

    // 已完成的跟團（代購團）- Order
    $completedOrders = \App\Models\Order::where('buyer_id', $userId)
        ->where('status', 'completed')
        ->with(['seller:id,name', 'source', 'items'])
        ->latest('updated_at')
        ->get();

    // 已完成的請託單 - 直接從 RequestList status=completed 找
    $completedRequestLists = \App\Models\RequestList::where('user_id', $userId)
        ->where('status', 'completed')
        ->with(['agent:id,name', 'items'])
        ->latest('updated_at')
        ->get();

    // 取每張 RequestList 對應的 Quote（不限狀態，只要有接過單的）
    $completedQuotes = \App\Models\Quote::whereIn('request_list_id', $completedRequestLists->pluck('id'))
        ->with(['user:id,name', 'requestList:id,title,user_id'])
        ->latest('updated_at')
        ->get()
        ->unique('request_list_id'); // 同一張請託單只取一筆 Quote

    // 已評價的紀錄
    $reviewedOrderIds = \App\Models\Review::where('reviewer_id', $userId)
        ->where('reviewable_type', \App\Models\Order::class)
        ->pluck('reviewable_id')->toArray();

    $reviewedQuoteIds = \App\Models\Review::where('reviewer_id', $userId)
        ->where('reviewable_type', \App\Models\Quote::class)
        ->pluck('reviewable_id')->toArray();
@endphp

<div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-1">評價中心</h2>
    <p class="text-sm text-gray-500 mb-6">針對已完成的訂單，為代購人留下評價。</p>

    {{-- Tab 切換 --}}
    <div x-data="{ reviewTab: 'orders' }">
        <div class="flex gap-2 mb-6 border-b border-gray-100 pb-3">
            <button type="button"
                @click="reviewTab = 'orders'"
                :class="reviewTab === 'orders' ? 'bg-purple-50 text-purple-700 border-purple-300 font-bold' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'"
                class="px-4 py-1.5 rounded-full border text-xs transition">
                跟團評價
            </button>
            <button type="button"
                @click="reviewTab = 'quotes'"
                :class="reviewTab === 'quotes' ? 'bg-green-50 text-green-700 border-green-300 font-bold' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'"
                class="px-4 py-1.5 rounded-full border text-xs transition">
                請託單評價
            </button>
        </div>

        {{-- 跟團評價（代購團） --}}
        <div x-show="reviewTab === 'orders'" class="space-y-4">
            @forelse($completedOrders as $order)
                @php
                    $hasReviewed = in_array($order->id, $reviewedOrderIds);
                    $existingReview = $hasReviewed
                        ? \App\Models\Review::where('reviewer_id', $userId)
                            ->where('reviewable_type', \App\Models\Order::class)
                            ->where('reviewable_id', $order->id)
                            ->first()
                        : null;
                @endphp
                <div class="rounded-xl border {{ $hasReviewed ? 'border-gray-100 bg-gray-50' : 'border-purple-100 bg-purple-50/30' }} p-4">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-sm font-bold text-gray-800">{{ $order->source->title ?? '代購團' }}</h4>
                                @if($hasReviewed)
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-bold bg-gray-100 text-gray-500 border-gray-200">已評價</span>
                                @else
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-bold bg-purple-50 text-purple-600 border-purple-200">待評價</span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                代購人：{{ $order->seller->name ?? '-' }} ・
                                訂單金額：NT$ {{ number_format($order->total_amount, 0) }}
                            </p>
                            @if($hasReviewed && $existingReview)
                                <div class="mt-2 flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $existingReview->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    @if($existingReview->comment)
                                        <span class="ml-2 text-xs text-gray-500">「{{ $existingReview->comment }}」</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <button type="button"
                                onclick="openReviewDetailModal('order-{{ $order->id }}')"
                                class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-200 transition">
                                查看詳細
                            </button>
                            @if(!$hasReviewed)
                                <button type="button"
                                    onclick="openReviewModal('order', {{ $order->id }}, '{{ addslashes($order->seller->name ?? '代購人') }}', '{{ addslashes($order->source->title ?? '代購團') }}')"
                                    class="inline-flex items-center rounded-lg bg-purple-600 px-3 py-2 text-xs font-bold text-white hover:bg-purple-700 transition">
                                    ✦ 寫評價
                                </button>
                            @else
                                <button type="button"
                                    onclick="openReviewModal('order', {{ $order->id }}, '{{ addslashes($order->seller->name ?? '代購人') }}', '{{ addslashes($order->source->title ?? '代購團') }}', true, {{ $existingReview->rating ?? 0 }}, '{{ addslashes($existingReview->comment ?? '') }}')"
                                    class="inline-flex items-center rounded-lg bg-amber-100 px-3 py-2 text-xs font-bold text-amber-700 hover:bg-amber-200 transition">
                                    查看評價
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- 查看詳細 Modal --}}
                <div id="review-detail-modal-order-{{ $order->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" onclick="if(event.target===this) closeReviewDetailModal('order-{{ $order->id }}')">
                    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl overflow-hidden">
                        <div class="flex items-start justify-between gap-3 border-b border-purple-100 bg-purple-50 px-5 py-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-purple-500">跟團完整資料</p>
                                <h4 class="mt-1 text-lg font-bold text-slate-800">{{ $order->source->title ?? '代購團' }}</h4>
                            </div>
                            <button onclick="closeReviewDetailModal('order-{{ $order->id }}')" class="rounded-full bg-white px-2.5 py-1 text-slate-500 ring-1 ring-slate-200 hover:bg-slate-50">✕</button>
                        </div>
                        <div class="max-h-[72vh] overflow-y-auto px-5 py-5 space-y-4">
                            @php $sourcePost = $order->source; @endphp
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">代購人</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ $order->seller->name ?? '-' }}</p></div>
                                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">狀態</p><p class="mt-1 text-sm font-semibold text-purple-700">已完成</p></div>
                                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">下單日期</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($order->created_at)->format('Y-m-d H:i') }}</p></div>
                                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">付款方式</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ ['linepay'=>'LINE Pay','bank'=>'超商付款','jkopay'=>'街口支付'][$order->payment_method] ?? ($order->payment_method ?: '未設定') }}</p></div>
                                @if($sourcePost && ($sourcePost->start_date || $sourcePost->end_date))
                                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">銷售期間</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($sourcePost->start_date)->format('Y-m-d') }} ～ {{ optional($sourcePost->end_date)->format('Y-m-d') }}</p></div>
                                @endif
                                @if($sourcePost && $sourcePost->country)
                                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">代購地區</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ $sourcePost->country }}{{ $sourcePost->city ? '・'.$sourcePost->city : '' }}</p></div>
                                @endif
                                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">物流單號</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ $order->tracking_number ?: '待更新' }}</p></div>
                                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">總金額</p><p class="mt-1 text-sm font-semibold text-slate-700">NT$ {{ number_format((float)$order->total_amount, 0) }}</p></div>
                            </div>
                            @if($sourcePost && $sourcePost->description)
                            <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">內容描述</p><p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $sourcePost->description }}</p></div>
                            @endif
                            {{-- 商品清單（含圖片）--}}
                            <section class="rounded-xl border border-slate-200 p-4">
                                <h5 class="text-sm font-bold text-slate-700 mb-3">商品清單</h5>
                                <div class="space-y-3">
                                    @forelse($order->items as $item)
                                        @php $product = $item->product_id ? \App\Models\PostProduct::find($item->product_id) : null; @endphp
                                        <div class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                                            <div class="w-20 h-20 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                                                @if($product && $product->display_image_url)
                                                    <img src="{{ $product->display_image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <i class="bi bi-image text-2xl text-gray-300"></i>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-slate-800 text-sm">{{ $item->name }}</p>
                                                <div class="mt-1.5 flex flex-wrap gap-x-4 gap-y-0.5 text-xs text-slate-500">
                                                    <span>單價：NT$ {{ number_format((float)$item->price, 0) }}</span>
                                                    <span>數量：{{ $item->quantity }} 件</span>
                                                    <span class="font-semibold text-indigo-600">小計：NT$ {{ number_format((float)$item->subtotal, 0) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="rounded-lg bg-slate-50 px-3 py-2 text-slate-400 text-sm">無商品資料</div>
                                    @endforelse
                                </div>
                            </section>
                            {{-- 已有評價則顯示 --}}
                            @php $existingReviewForModal = \App\Models\Review::where('reviewer_id', $userId)->where('reviewable_type', \App\Models\Order::class)->where('reviewable_id', $order->id)->first(); @endphp
                            @if($existingReviewForModal)
                            <div class="rounded-xl border border-amber-100 bg-amber-50 p-4">
                                <p class="text-xs font-bold text-amber-700 mb-2">我的評價</p>
                                <div class="flex items-center gap-1 mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $existingReviewForModal->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                                @if($existingReviewForModal->comment)<p class="text-sm text-slate-600">「{{ $existingReviewForModal->comment }}」</p>@endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-gray-400 text-sm text-center py-10 border border-dashed border-gray-200 rounded-xl">
                    目前沒有可評價的跟團。
                </div>
            @endforelse
        </div>

        {{-- 請託單評價（以已完成的請託單為基準）--}}
        <div x-show="reviewTab === 'quotes'" class="space-y-4">
            @forelse($completedRequestLists as $rl)
                @php
                    // 找這張請託單對應的 Quote
                    $rlQuote = $completedQuotes->firstWhere('request_list_id', $rl->id);
                    // 用 RequestList ID 做評價識別（reviewable 是 RequestList）
                    $hasReviewed = \App\Models\Review::where('reviewer_id', $userId)
                        ->where('reviewable_type', \App\Models\RequestList::class)
                        ->where('reviewable_id', $rl->id)
                        ->exists();
                    $existingReview = $hasReviewed
                        ? \App\Models\Review::where('reviewer_id', $userId)
                            ->where('reviewable_type', \App\Models\RequestList::class)
                            ->where('reviewable_id', $rl->id)
                            ->first()
                        : null;
                    $agentName = optional($rl->agent)->name ?? optional($rlQuote?->user)->name ?? '未指定代購人';
                    $agentId   = $rl->people ?? $rlQuote?->user_id;
                @endphp
                <div class="rounded-xl border {{ $hasReviewed ? 'border-gray-100 bg-gray-50' : 'border-green-100 bg-green-50/30' }} p-4">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-sm font-bold text-gray-800">{{ $rl->title }}</h4>
                                @if($hasReviewed)
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-bold bg-gray-100 text-gray-500 border-gray-200">已評價</span>
                                @else
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-bold bg-green-50 text-green-600 border-green-200">待評價</span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                代購人：{{ $agentName }}
                                @if($rlQuote)
                                    ・ 報價金額：NT$ {{ number_format((float)($rl->agent_quote_total ?? $rlQuote->price ?? 0), 0) }}
                                @endif
                            </p>
                            @if($hasReviewed && $existingReview)
                                <div class="mt-2 flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $existingReview->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    @if($existingReview->comment)
                                        <span class="ml-2 text-xs text-gray-500">「{{ $existingReview->comment }}」</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <button type="button"
                                onclick="openReviewDetailModal('rl-{{ $rl->id }}')"
                                class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-200 transition">
                                查看詳細
                            </button>
                            @if(!$hasReviewed && $agentId)
                                <button type="button"
                                    onclick="openReviewModal('request-list', {{ $rl->id }}, '{{ addslashes($agentName) }}', '{{ addslashes($rl->title) }}')"
                                    class="inline-flex items-center rounded-lg bg-green-600 px-3 py-2 text-xs font-bold text-white hover:bg-green-700 transition">
                                    ✦ 寫評價
                                </button>
                            @elseif($hasReviewed)
                                <button type="button"
                                    onclick="openReviewModal('request-list', {{ $rl->id }}, '{{ addslashes($agentName) }}', '{{ addslashes($rl->title) }}', true, {{ $existingReview->rating ?? 0 }}, '{{ addslashes($existingReview->comment ?? '') }}')"
                                    class="inline-flex items-center rounded-lg bg-amber-100 px-3 py-2 text-xs font-bold text-amber-700 hover:bg-amber-200 transition">
                                    查看評價
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- 查看詳細 Modal --}}
                <div id="review-detail-modal-rl-{{ $rl->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" onclick="if(event.target===this) closeReviewDetailModal('rl-{{ $rl->id }}')">
                    <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden">
                        <div class="flex items-start justify-between border-b border-gray-100 px-5 py-4 bg-gray-50">
                            <div>
                                <p class="text-xs font-semibold text-green-600 uppercase">請託單詳細資訊</p>
                                <h4 class="mt-1 text-base font-bold text-gray-800">{{ $rl->title }}</h4>
                            </div>
                            <button onclick="closeReviewDetailModal('rl-{{ $rl->id }}')" class="text-gray-400 hover:text-gray-600 text-xl">✕</button>
                        </div>
                        <div class="max-h-[72vh] overflow-y-auto px-5 py-4 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">代購人</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ $agentName }}</p></div>
                                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">狀態</p><p class="mt-1 text-sm font-semibold text-slate-700">已完成</p></div>
                                @if($rlQuote)
                                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">報價金額</p><p class="mt-1 text-sm font-semibold text-slate-700">NT$ {{ number_format((float)($rl->agent_quote_total ?? $rlQuote->price ?? 0), 0) }}</p></div>
                                @endif
                                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">截止日期</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($rl->deadline)->format('Y-m-d') ?? '-' }}</p></div>
                                @if($rl->country)
                                <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">代購地區</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ $rl->country }} {{ $rl->city ?? '' }}</p></div>
                                @endif
                                @if($rl->store_name)
                                <div class="rounded-xl bg-slate-50 px-4 py-3 col-span-2"><p class="text-xs text-slate-400">店家名稱</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ $rl->store_name }}</p></div>
                                @endif
                                @if($rl->detail_address)
                                <div class="rounded-xl bg-slate-50 px-4 py-3 col-span-2"><p class="text-xs text-slate-400">詳細地址</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ $rl->detail_address }}</p></div>
                                @endif
                                @if($rl->note)
                                <div class="rounded-xl bg-slate-50 px-4 py-3 col-span-2"><p class="text-xs text-slate-400">備註</p><p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $rl->note }}</p></div>
                                @endif
                            </div>

                            {{-- 商品清單（含圖片）--}}
                            @if($rl->items->count())
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-bold text-slate-600 mb-3">商品清單</p>
                                @foreach($rl->items as $item)
                                    <div class="flex items-center gap-3 rounded-lg bg-slate-50 px-3 py-2.5 mb-2 last:mb-0">
                                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-300">
                                            @if($item->reference_image)
                                                <img src="{{ route('request-item.image', $item) }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                            @else
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-slate-700 truncate">{{ $item->product_name ?? $item->name ?? '未命名商品' }}</p>
                                            <div class="mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-slate-500">
                                                <span>數量：{{ $item->quantity }}</span>
                                                @if(!empty($item->expected_price))
                                                    <span>期望單價：NT$ {{ number_format((float)$item->expected_price, 0) }}</span>
                                                @endif
                                                @if(!empty($item->specification))
                                                    <span>規格：{{ $item->specification }}</span>
                                                @endif
                                            </div>
                                            @if(!empty($item->reference_url))
                                                <a href="{{ $item->reference_url }}" target="_blank" class="mt-0.5 inline-block text-xs text-blue-500 hover:underline truncate max-w-full">參考連結 ↗</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @endif

                            {{-- 已有評價則顯示 --}}
                            @php $existingReviewForRlModal = \App\Models\Review::where('reviewer_id', $userId)->where('reviewable_type', \App\Models\RequestList::class)->where('reviewable_id', $rl->id)->first(); @endphp
                            @if($existingReviewForRlModal)
                            <div class="rounded-xl border border-amber-100 bg-amber-50 p-4">
                                <p class="text-xs font-bold text-amber-700 mb-2">我的評價</p>
                                <div class="flex items-center gap-1 mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $existingReviewForRlModal->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                                @if($existingReviewForRlModal->comment)<p class="text-sm text-slate-600">「{{ $existingReviewForRlModal->comment }}」</p>@endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-gray-400 text-sm text-center py-10 border border-dashed border-gray-200 rounded-xl">
                    目前沒有可評價的請託。
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- 評價 Modal --}}
<div id="review-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
     onclick="if(event.target===this){closeReviewModal();}">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-bold text-gray-800">寫下評價</h4>
            <button type="button" onclick="closeReviewModal()" class="text-2xl text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <div class="mb-4 p-3 bg-gray-50 rounded-xl">
            <p class="text-xs text-gray-500">評價對象</p>
            <p id="review-modal-reviewee" class="text-sm font-bold text-gray-800 mt-0.5"></p>
            <p id="review-modal-source" class="text-xs text-gray-500 mt-0.5"></p>
        </div>
        <form id="review-form" method="POST" action="{{ route('reviews.store') }}">
            @csrf
            <input type="hidden" name="reviewable_type" id="review-type-input">
            <input type="hidden" name="reviewable_id" id="review-id-input">

            {{-- 星星評分 --}}
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-600 mb-2">評分</label>
                <div class="flex gap-2" id="star-group">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" data-star="{{ $i }}"
                            onclick="setRating({{ $i }})"
                            class="star-btn text-3xl text-gray-200 hover:text-amber-400 transition">★</button>
                    @endfor
                </div>
                <input type="hidden" name="rating" id="rating-input" value="">
            </div>

            {{-- 文字評論 --}}
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-600 mb-1">評論（選填）</label>
                <textarea name="comment" rows="3" maxlength="500"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-purple-500 focus:ring-purple-500"
                    placeholder="分享您的購物體驗..."></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeReviewModal()"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-200 transition">
                    取消
                </button>
                <button type="submit" id="review-submit-btn"
                    class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-white hover:bg-amber-600 transition disabled:opacity-50"
                    disabled>
                    送出評價
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openReviewModal(type, id, reviewee, source, viewOnly = false, existingRating = 0, existingComment = '') {
    document.getElementById('review-type-input').value = type;
    document.getElementById('review-id-input').value = id;
    document.getElementById('review-modal-reviewee').textContent = reviewee;
    document.getElementById('review-modal-source').textContent = source;

    const submitBtn = document.getElementById('review-submit-btn');
    const title = document.querySelector('#review-modal h4');
    const textarea = document.querySelector('#review-form textarea[name=comment]');
    const starGroup = document.getElementById('star-group');

    if (viewOnly) {
        title.textContent = '我的評價';
        submitBtn.style.display = 'none';
        // 顯示已有的評分
        setRating(existingRating);
        // 星星改成唯讀（不可點）
        starGroup.querySelectorAll('.star-btn').forEach(btn => {
            btn.style.pointerEvents = 'none';
        });
        // 顯示已有的評論
        textarea.value = existingComment;
        textarea.readOnly = true;
        textarea.classList.add('bg-gray-50', 'text-gray-500');
    } else {
        title.textContent = '寫下評價';
        submitBtn.style.display = '';
        setRating(0);
        starGroup.querySelectorAll('.star-btn').forEach(btn => {
            btn.style.pointerEvents = '';
        });
        textarea.value = '';
        textarea.readOnly = false;
        textarea.classList.remove('bg-gray-50', 'text-gray-500');
    }

    document.getElementById('review-modal').classList.remove('hidden');
    document.getElementById('review-modal').classList.add('flex');
}

function closeReviewModal() {
    document.getElementById('review-modal').classList.add('hidden');
    document.getElementById('review-modal').classList.remove('flex');
    document.getElementById('review-submit-btn').style.display = '';
    const textarea = document.querySelector('#review-form textarea[name=comment]');
    textarea.readOnly = false;
    textarea.classList.remove('bg-gray-50', 'text-gray-500');
    document.getElementById('star-group').querySelectorAll('.star-btn').forEach(btn => {
        btn.style.pointerEvents = '';
    });
}

function openReviewDetailModal(key) {
    const modal = document.getElementById('review-detail-modal-' + key);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeReviewDetailModal(key) {
    const modal = document.getElementById('review-detail-modal-' + key);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function setRating(value) {
    document.getElementById('rating-input').value = value;
    document.querySelectorAll('.star-btn').forEach(btn => {
        btn.classList.toggle('text-amber-400', parseInt(btn.dataset.star) <= value);
        btn.classList.toggle('text-gray-200', parseInt(btn.dataset.star) > value);
    });
    document.getElementById('review-submit-btn').disabled = value === 0;
}
</script>