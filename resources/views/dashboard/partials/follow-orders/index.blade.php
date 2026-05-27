<div class="bg-white rounded-2xl shadow-sm p-6" x-data="{ followTab: 'unpaid' }">
                            <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">跟團</h3>
                                    <p class="mt-1 text-sm text-gray-500">這裡會顯示你所有跟團與目前配送狀態。</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <form method="GET" action="{{ route('dashboard') }}" class="relative w-full md:w-80">
    <input type="hidden" name="section" value="follow-orders">
    <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#bb63f1] transition">
        <i class="bi bi-search"></i>
    </button>
    <input type="search" name="follow_search" placeholder="搜尋貼文標題、代購人..."
        value="{{ request('follow_search') }}"
        class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#bb63f1] focus:border-[#bb63f1] shadow-sm transition outline-none">
</form>
                                </div>
                            </div>

                            {{-- Tab 切換 --}}
                            @php
                                $unpaidOrders   = $followOrders->filter(fn($o) => in_array($o->status, ['pending_payment']));
                                $shippingOrders = $followOrders->filter(fn($o) => in_array($o->status, ['wait-for-ship', 'shipped']));
                                $arrivedOrders  = $followOrders->filter(fn($o) => in_array($o->status, ['arrivaled', 'completed']));
                            @endphp
                            <div class="flex gap-2 mb-5 border-b border-gray-100 pb-3">
                                <button type="button"
                                    @click="followTab = 'unpaid'"
                                    :class="followTab === 'unpaid' ? 'bg-purple-50 text-purple-700 border-purple-300 font-bold' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'"
                                    class="px-4 py-1.5 rounded-full border text-xs transition">
                                    未付款
                                    @if($unpaidOrders->count() > 0)
                                        <span class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-purple-500 text-white text-[10px]">{{ $unpaidOrders->count() }}</span>
                                    @endif
                                </button>
                                <button type="button"
                                    @click="followTab = 'shipping'"
                                    :class="followTab === 'shipping' ? 'bg-blue-50 text-blue-700 border-blue-300 font-bold' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'"
                                    class="px-4 py-1.5 rounded-full border text-xs transition">
                                    待出貨/已出貨
                                    @if($shippingOrders->count() > 0)
                                        <span class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-500 text-white text-[10px]">{{ $shippingOrders->count() }}</span>
                                    @endif
                                </button>
                                <button type="button"
                                    @click="followTab = 'arrived'"
                                    :class="followTab === 'arrived' ? 'bg-green-50 text-green-700 border-green-300 font-bold' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'"
                                    class="px-4 py-1.5 rounded-full border text-xs transition">
                                    已到貨
                                    @if($arrivedOrders->count() > 0)
                                        <span class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-green-500 text-white text-[10px]">{{ $arrivedOrders->count() }}</span>
                                    @endif
                                </button>
                            </div>

                            @if(request('follow_search'))
                                <div class="mb-4 rounded-lg border border-purple-200 bg-purple-50 px-4 py-3 text-sm text-purple-700">
                                    搜尋「{{ request('follow_search') }}」找到 {{ $followOrders->total() }} 筆跟團。
                                    <a href="{{ route('dashboard', ['section' => 'follow-orders']) }}" class="ml-2 font-semibold hover:underline">清除搜尋</a>
                                </div>
                            @endif

                            {{-- 未付款 --}}
                            <div x-show="followTab === 'unpaid'" class="space-y-2.5">
                                @if($unpaidOrders->isEmpty())
                                    <div class="text-gray-400 text-sm text-center py-10 border border-dashed border-gray-200 rounded-xl">目前沒有未付款的跟團。</div>
                                @else
                                @foreach($unpaidOrders as $followOrder)
                                    @php
                                        $followOrderTitle = $followOrder->source?->title
                                            ?? data_get($followOrder->recipient_data, 'post_title')
                                            ?? '未命名貼文';
                                    @endphp
                                    <article class="flex flex-col gap-2.5 rounded-[20px] border border-purple-100 bg-[#fdf8ff] px-3.5 py-3 shadow-sm lg:flex-row lg:items-center">
                                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[18px] bg-white text-purple-400 shadow-sm">
                                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                             <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                                                <div class="min-w-0 flex-1">
                                                    <h4 class="truncate text-[0.9rem] font-semibold text-slate-700">{{ $followOrderTitle }}</h4>
                                                    <div class="mt-1 flex flex-wrap items-center gap-x-6 gap-y-1 text-[0.82rem] text-slate-600">
                                                        <span>代購人：{{ optional($followOrder->seller)->name ?? '未指定代購人' }}</span>
                                                        <span>
                                                            代購商品：
                                                            {{ $followOrder->items->map(fn ($item) => $item->name . ' × ' . $item->quantity)->implode('、') ?: '無商品資料' }}
                                                        </span>
                                                    </div>
                                                    <div class="mt-1 flex flex-wrap items-center gap-x-5 gap-y-1 text-[0.82rem] text-slate-500">
                                                        <span>下單日期：{{ optional($followOrder->created_at)->format('Y-m-d') }}</span>
                                                        <span>商品數量：{{ $followOrder->items->sum('quantity') }} 件</span>
                                                        <span>總金額：{{ number_format((float) $followOrder->total_amount, 0) }} {{ $followOrder->currency }}</span>
                                                    </div>
                                                    
                    
                                                </div>

                                                 <div class="flex items-center justify-between gap-2 lg:justify-end">
                                                    @php
                                                        $statusText = match ($followOrder->status) {
                                                            'pending_payment' => '待付款',
                                                                'wait-for-ship' => '等待出貨',
                                                                'shipped' => '已出貨',
                                                                'arrivaled' => '已到貨',
                                                                'completed' => '已完成',
                                                            'cancelled' => '已取消',
                                                            'refunded' => '已退款',
                                                            default => $followOrder->status,
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-[0.8rem] font-semibold text-purple-700">
                                                        {{ $statusText }}
                                                    </span>
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center rounded-full bg-white px-3 py-1 text-[0.8rem] font-semibold text-purple-600 shadow-sm ring-1 ring-purple-200 transition hover:bg-purple-50"
                                                        onclick="openFollowOrderModal({{ $followOrder->id }})"
                                                    >
                                                        檢視
                                                    </button>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </article>

                                    {{-- 檢視 Modal（未付款） --}}
                                    @php
                                        $followOrderStatusText = '待付款';
                                    @endphp
                                    <div id="follow-order-modal-{{ $followOrder->id }}" class="follow-order-modal hidden fixed inset-0 z-[72] flex items-center justify-center bg-slate-900/55 px-4 py-6" onclick="handleFollowOrderBackdrop(event, {{ $followOrder->id }})">
                                        <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                                            <div class="flex items-start justify-between gap-3 border-b border-purple-100 bg-purple-50 px-5 py-4">
                                                <div>
                                                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-500">跟團完整資料</p>
                                                    <h4 class="mt-1 text-lg font-bold text-slate-800">{{ $followOrderTitle }}</h4>
                                                </div>
                                                <button type="button" class="rounded-full bg-white px-2.5 py-1 text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-50" onclick="closeFollowOrderModal({{ $followOrder->id }})" aria-label="關閉">✕</button>
                                            </div>
                                            <div class="max-h-[72vh] overflow-y-auto px-5 py-5">
                                                @php $sourcePost = $followOrder->source; @endphp
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">代購人</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($followOrder->seller)->name ?? '-' }}</p></div>
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">訂單狀態</p><p class="mt-1 text-sm font-semibold text-purple-700">{{ $followOrderStatusText }}</p></div>
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">下單日期</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($followOrder->created_at)->format('Y-m-d H:i') }}</p></div>
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">付款方式</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ ['linepay'=>'LINE Pay','bank'=>'超商付款','jkopay'=>'街口支付'][$followOrder->payment_method] ?? ($followOrder->payment_method ?: '未設定') }}</p></div>
                                                    @if($sourcePost && ($sourcePost->start_date || $sourcePost->end_date))
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">銷售期間</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($sourcePost->start_date)->format('Y-m-d') }} ～ {{ optional($sourcePost->end_date)->format('Y-m-d') }}</p></div>
                                                    @endif
                                                    @if($sourcePost && $sourcePost->country)
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">代購地區</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ $sourcePost->country }}{{ $sourcePost->city ? '・'.$sourcePost->city : '' }}</p></div>
                                                    @endif
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">物流單號</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ $followOrder->tracking_number ?: '待更新' }}</p></div>
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">總金額</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ number_format((float)$followOrder->total_amount, 0) }} {{ $followOrder->currency }}</p></div>
                                                </div>
                                                @php $postDescription = $followOrder->source?->description ?? null; @endphp
                                                <div class="mt-3 rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">內容描述</p><p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $postDescription ?: '-' }}</p></div>
                                                <section class="mt-4 rounded-xl border border-slate-200 p-4">
                                                    <h5 class="text-sm font-bold text-slate-700 mb-3">商品清單</h5>
                                                    <div class="space-y-3">
                                                        @forelse($followOrder->items as $item)
                                                            @php $product = $item->product_id ? \App\Models\PostProduct::find($item->product_id) : null; @endphp
                                                            <div class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                                                                <div class="w-20 h-20 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                                                                    @if($product && $product->display_image_url)<img src="{{ $product->display_image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">@else<i class="bi bi-image text-2xl text-gray-300"></i>@endif
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
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @endif
                            </div>

                            {{-- 待出貨 --}}
                            <div x-show="followTab === 'shipping'" class="space-y-2.5">
                                @if($shippingOrders->isEmpty())
                                    <div class="text-gray-400 text-sm text-center py-10 border border-dashed border-gray-200 rounded-xl">目前沒有待出貨的跟團。</div>
                                @else
                                @foreach($shippingOrders as $followOrder)
                                    @php
                                        $followOrderTitle = $followOrder->source?->title
                                            ?? data_get($followOrder->recipient_data, 'post_title')
                                            ?? '未命名貼文';
                                        $followOrderStatusText = match ($followOrder->status) {
                                            'wait-for-ship' => '等待出貨',
                                            'shipped'       => '已出貨',
                                            default         => $followOrder->status,
                                        };
                                    @endphp
                                    <article class="flex flex-col gap-2.5 rounded-[20px] border border-blue-100 bg-blue-50/40 px-3.5 py-3 shadow-sm lg:flex-row lg:items-center">
                                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[18px] bg-white text-blue-400 shadow-sm">
                                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                                                <div class="min-w-0 flex-1">
                                                    <h4 class="truncate text-[0.9rem] font-semibold text-slate-700">{{ $followOrderTitle }}</h4>
                                                    <div class="mt-1 flex flex-wrap items-center gap-x-6 gap-y-1 text-[0.82rem] text-slate-600">
                                                        <span>代購人：{{ optional($followOrder->seller)->name ?? '未指定代購人' }}</span>
                                                        <span>代購商品：{{ $followOrder->items->map(fn ($item) => $item->name . ' × ' . $item->quantity)->implode('、') ?: '無商品資料' }}</span>
                                                    </div>
                                                    <div class="mt-1 flex flex-wrap items-center gap-x-5 gap-y-1 text-[0.82rem] text-slate-500">
                                                        <span>下單日期：{{ optional($followOrder->created_at)->format('Y-m-d') }}</span>
                                                        <span>商品數量：{{ $followOrder->items->sum('quantity') }} 件</span>
                                                        <span>總金額：{{ number_format((float) $followOrder->total_amount, 0) }} {{ $followOrder->currency }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between gap-2 lg:justify-end">
                                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-blue-50 text-blue-700 border-blue-200">{{ $followOrderStatusText }}</span>
                                                    <button type="button"
                                                        onclick="document.getElementById('follow-order-modal-{{ $followOrder->id }}').classList.remove('hidden'); document.getElementById('follow-order-modal-{{ $followOrder->id }}').classList.add('flex');"
                                                        class="rounded-full border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-gray-50">
                                                        檢視
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </article>

                                    {{-- 檢視 Modal（待出貨） --}}
                                    @php
                                        $followOrderStatusText = match ($followOrder->status) {
                                            'wait-for-ship' => '等待出貨', 'shipped' => '已出貨', default => $followOrder->status,
                                        };
                                    @endphp
                                    <div id="follow-order-modal-{{ $followOrder->id }}" class="follow-order-modal hidden fixed inset-0 z-[72] flex items-center justify-center bg-slate-900/55 px-4 py-6" onclick="handleFollowOrderBackdrop(event, {{ $followOrder->id }})">
                                        <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                                            <div class="flex items-start justify-between gap-3 border-b border-blue-100 bg-blue-50 px-5 py-4">
                                                <div>
                                                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-500">跟團完整資料</p>
                                                    <h4 class="mt-1 text-lg font-bold text-slate-800">{{ $followOrderTitle }}</h4>
                                                </div>
                                                <button type="button" class="rounded-full bg-white px-2.5 py-1 text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-50" onclick="closeFollowOrderModal({{ $followOrder->id }})" aria-label="關閉">✕</button>
                                            </div>
                                            <div class="max-h-[72vh] overflow-y-auto px-5 py-5">
                                                @php $sourcePost = $followOrder->source; @endphp
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">代購人</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($followOrder->seller)->name ?? '-' }}</p></div>
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">訂單狀態</p><p class="mt-1 text-sm font-semibold text-blue-700">{{ $followOrderStatusText }}</p></div>
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">下單日期</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($followOrder->created_at)->format('Y-m-d H:i') }}</p></div>
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">付款方式</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ ['linepay'=>'LINE Pay','bank'=>'超商付款','jkopay'=>'街口支付'][$followOrder->payment_method] ?? ($followOrder->payment_method ?: '未設定') }}</p></div>
                                                    @if($sourcePost && ($sourcePost->start_date || $sourcePost->end_date))
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">銷售期間</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($sourcePost->start_date)->format('Y-m-d') }} ～ {{ optional($sourcePost->end_date)->format('Y-m-d') }}</p></div>
                                                    @endif
                                                    @if($sourcePost && $sourcePost->country)
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">代購地區</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ $sourcePost->country }}{{ $sourcePost->city ? '・'.$sourcePost->city : '' }}</p></div>
                                                    @endif
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">物流單號</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ $followOrder->tracking_number ?: '待更新' }}</p></div>
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">總金額</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ number_format((float)$followOrder->total_amount, 0) }} {{ $followOrder->currency }}</p></div>
                                                </div>
                                                @php $postDescription = $followOrder->source?->description ?? null; @endphp
                                                <div class="mt-3 rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">內容描述</p><p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $postDescription ?: '-' }}</p></div>
                                                <section class="mt-4 rounded-xl border border-slate-200 p-4">
                                                    <h5 class="text-sm font-bold text-slate-700 mb-3">商品清單</h5>
                                                    <div class="space-y-3">
                                                        @forelse($followOrder->items as $item)
                                                            @php $product = $item->product_id ? \App\Models\PostProduct::find($item->product_id) : null; @endphp
                                                            <div class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                                                                <div class="w-20 h-20 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                                                                    @if($product && $product->display_image_url)<img src="{{ $product->display_image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">@else<i class="bi bi-image text-2xl text-gray-300"></i>@endif
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
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @endif
                            </div>

                            {{-- 已到貨 --}}
                            <div x-show="followTab === 'arrived'" class="space-y-2.5">
                                @if($arrivedOrders->isEmpty())
                                    <div class="text-gray-400 text-sm text-center py-10 border border-dashed border-gray-200 rounded-xl">目前沒有已到貨的跟團。</div>
                                @else
                                @foreach($arrivedOrders as $followOrder)
                                    @php
                                        $followOrderTitle = $followOrder->source?->title
                                            ?? data_get($followOrder->recipient_data, 'post_title')
                                            ?? '未命名貼文';
                                        $followOrderStatusText = match ($followOrder->status) {
                                            'arrivaled' => '已到貨',
                                            'completed' => '已完成',
                                            default     => $followOrder->status,
                                        };
                                    @endphp
                                    <article class="flex flex-col gap-2.5 rounded-[20px] border border-green-100 bg-green-50/40 px-3.5 py-3 shadow-sm lg:flex-row lg:items-center">
                                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[18px] bg-white text-green-400 shadow-sm">
                                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                                                <div class="min-w-0 flex-1">
                                                    <h4 class="truncate text-[0.9rem] font-semibold text-slate-700">{{ $followOrderTitle }}</h4>
                                                    <div class="mt-1 flex flex-wrap items-center gap-x-6 gap-y-1 text-[0.82rem] text-slate-600">
                                                        <span>代購人：{{ optional($followOrder->seller)->name ?? '未指定代購人' }}</span>
                                                        <span>代購商品：{{ $followOrder->items->map(fn ($item) => $item->name . ' × ' . $item->quantity)->implode('、') ?: '無商品資料' }}</span>
                                                    </div>
                                                    <div class="mt-1 flex flex-wrap items-center gap-x-5 gap-y-1 text-[0.82rem] text-slate-500">
                                                        <span>下單日期：{{ optional($followOrder->created_at)->format('Y-m-d') }}</span>
                                                        <span>商品數量：{{ $followOrder->items->sum('quantity') }} 件</span>
                                                        <span>總金額：{{ number_format((float) $followOrder->total_amount, 0) }} {{ $followOrder->currency }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between gap-2 lg:justify-end">
                                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold bg-green-50 text-green-700 border-green-200">{{ $followOrderStatusText }}</span>
                                                    <button type="button"
                                                        onclick="document.getElementById('follow-order-modal-{{ $followOrder->id }}').classList.remove('hidden'); document.getElementById('follow-order-modal-{{ $followOrder->id }}').classList.add('flex');"
                                                        class="rounded-full border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-gray-50">
                                                        檢視
                                                    </button>

                                                    @if($followOrder->status === 'arrivaled')
                                                        <form method="POST" action="{{ route('orders.complete', $followOrder) }}" onsubmit="return confirm('是否已完成？');">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button
                                                                type="submit"
                                                                class="inline-flex items-center rounded-full bg-emerald-500 px-3 py-1 text-[0.8rem] font-semibold text-white shadow-sm transition hover:bg-emerald-600"
                                                            >
                                                                完成
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </article>

                                    {{-- 檢視 Modal（已到貨） --}}
                                    <div id="follow-order-modal-{{ $followOrder->id }}" class="follow-order-modal hidden fixed inset-0 z-[72] flex items-center justify-center bg-slate-900/55 px-4 py-6" onclick="handleFollowOrderBackdrop(event, {{ $followOrder->id }})">
                                        <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                                            <div class="flex items-start justify-between gap-3 border-b border-green-100 bg-green-50 px-5 py-4">
                                                <div>
                                                    <p class="text-xs font-semibold uppercase tracking-wider text-green-600">跟團完整資料</p>
                                                    <h4 class="mt-1 text-lg font-bold text-slate-800">{{ $followOrderTitle }}</h4>
                                                </div>
                                                <button type="button" class="rounded-full bg-white px-2.5 py-1 text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-50" onclick="closeFollowOrderModal({{ $followOrder->id }})" aria-label="關閉">✕</button>
                                            </div>
                                            <div class="max-h-[72vh] overflow-y-auto px-5 py-5">
                                                @php $sourcePost = $followOrder->source; @endphp
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">代購人</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($followOrder->seller)->name ?? '-' }}</p></div>
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">訂單狀態</p><p class="mt-1 text-sm font-semibold text-green-700">{{ $followOrderStatusText }}</p></div>
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">下單日期</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($followOrder->created_at)->format('Y-m-d H:i') }}</p></div>
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">付款方式</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ ['linepay'=>'LINE Pay','bank'=>'超商付款','jkopay'=>'街口支付'][$followOrder->payment_method] ?? ($followOrder->payment_method ?: '未設定') }}</p></div>
                                                    @if($sourcePost && ($sourcePost->start_date || $sourcePost->end_date))
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">銷售期間</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($sourcePost->start_date)->format('Y-m-d') }} ～ {{ optional($sourcePost->end_date)->format('Y-m-d') }}</p></div>
                                                    @endif
                                                    @if($sourcePost && $sourcePost->country)
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">代購地區</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ $sourcePost->country }}{{ $sourcePost->city ? '・'.$sourcePost->city : '' }}</p></div>
                                                    @endif
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">物流單號</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ $followOrder->tracking_number ?: '待更新' }}</p></div>
                                                    <div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">總金額</p><p class="mt-1 text-sm font-semibold text-slate-700">{{ number_format((float)$followOrder->total_amount, 0) }} {{ $followOrder->currency }}</p></div>
                                                </div>
                                                @php $postDescription = $followOrder->source?->description ?? null; @endphp
                                                <div class="mt-3 rounded-xl bg-slate-50 px-4 py-3"><p class="text-xs text-slate-400">內容描述</p><p class="mt-1 text-sm text-slate-700 whitespace-pre-line">{{ $postDescription ?: '-' }}</p></div>
                                                <section class="mt-4 rounded-xl border border-slate-200 p-4">
                                                    <h5 class="text-sm font-bold text-slate-700 mb-3">商品清單</h5>
                                                    <div class="space-y-3">
                                                        @forelse($followOrder->items as $item)
                                                            @php $product = $item->product_id ? \App\Models\PostProduct::find($item->product_id) : null; @endphp
                                                            <div class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                                                                <div class="w-20 h-20 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                                                                    @if($product && $product->display_image_url)<img src="{{ $product->display_image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover">@else<i class="bi bi-image text-2xl text-gray-300"></i>@endif
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
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @endif
                            </div>
                        </div>