                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">跟單紀錄</h3>
                                    <p class="mt-1 text-sm text-gray-500">這裡會顯示你所有跟單貼文與目前配送狀態。</p>
                                </div>

                                <div class="flex items-center gap-4">
                                    <form method="GET" action="{{ route('dashboard') }}" style="display: flex; gap: 8px; min-width: 280px;">
                                        <input type="hidden" name="section" value="follow-orders">
                                        <input
                                            type="search"
                                            name="follow_search"
                                            placeholder="搜尋貼文標題、代購人..."
                                            value="{{ request('follow_search') }}"
                                            style="padding: 8px 12px; border: 2px solid #0e0e0f; border-radius: 8px; font-size: 14px; min-width: 220px; flex: 1;"
                                        >
                                        <button type="submit" style="padding: 8px 16px; background: #bb63f1; color: white; border: none; border-radius: 8px; cursor: pointer;">
                                            🔍
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if(request('follow_search'))
                                <div class="mb-4 rounded-lg border border-purple-200 bg-purple-50 px-4 py-3 text-sm text-purple-700">
                                    搜尋「{{ request('follow_search') }}」找到 {{ $followOrders->total() }} 筆跟單紀錄。
                                    <a href="{{ route('dashboard', ['section' => 'follow-orders']) }}" class="ml-2 font-semibold hover:underline">清除搜尋</a>
                                </div>
                            @endif

                            <div class="space-y-2.5">
                                @forelse($followOrders as $followOrder)
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
                                                            'paid' => '已付款',
                                                            'purchasing' => '採購中',
                                                            'shipped' => '已出貨',
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
                                @empty
                                    <div class="rounded-2xl border border-dashed border-purple-200 bg-purple-50/40 px-6 py-12 text-center text-sm text-gray-500">
                                        @if(request('follow_search'))
                                            找不到符合「{{ request('follow_search') }}」的跟單紀錄。
                                        @else
                                            目前尚無跟單紀錄，快去首頁找到喜歡的代購商品並建立第一筆跟單吧！
                                        @endif
                                    </div>
                                @endforelse
                            </div>

                            @if($followOrders->hasPages())
                                <div class="mt-6">
                                    {{ $followOrders->appends(['section' => 'follow-orders', 'follow_search' => request('follow_search')])->links() }}
                                </div>
                            @endif

                            @foreach($followOrders as $followOrder)
                                @php
                                    $followOrderTitle = $followOrder->source?->title
                                        ?? data_get($followOrder->recipient_data, 'post_title')
                                        ?? '未命名貼文';
                                    $followOrderStatusText = match ($followOrder->status) {
                                        'pending_payment' => '待付款',
                                        'paid' => '已付款',
                                        'purchasing' => '採購中',
                                        'shipped' => '已出貨',
                                        'completed' => '已完成',
                                        'cancelled' => '已取消',
                                        'refunded' => '已退款',
                                        default => $followOrder->status,
                                    };
                                @endphp
                                <div id="follow-order-modal-{{ $followOrder->id }}" class="follow-order-modal hidden fixed inset-0 z-[72] flex items-center justify-center bg-slate-900/55 px-4 py-6" onclick="handleFollowOrderBackdrop(event, {{ $followOrder->id }})">
                                    <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                                        <div class="flex items-start justify-between gap-3 border-b border-purple-100 bg-purple-50 px-5 py-4">
                                            <div>
                                                <p class="text-xs font-semibold uppercase tracking-wider text-purple-500">跟單完整資料</p>
                                                <h4 class="mt-1 text-lg font-bold text-slate-800">{{ $followOrderTitle }}</h4>
                                            </div>
                                            <button type="button" class="rounded-full bg-white px-2.5 py-1 text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-50" onclick="closeFollowOrderModal({{ $followOrder->id }})" aria-label="關閉跟單完整資料視窗">✕</button>
                                        </div>

                                        <div class="max-h-[72vh] overflow-y-auto px-5 py-5">
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                                    <p class="text-xs text-slate-400">代購人</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($followOrder->seller)->name ?? '未指定代購人' }}</p>
                                                </div>
                                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                                    <p class="text-xs text-slate-400">訂單狀態</p>
                                                    <p class="mt-1 text-sm font-semibold text-indigo-700">{{ $followOrderStatusText }}</p>
                                                </div>
                                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                                    <p class="text-xs text-slate-400">下單日期</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($followOrder->created_at)->format('Y-m-d H:i') ?? '-' }}</p>
                                                </div>
                                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                                    <p class="text-xs text-slate-400">付款方式</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-700">{{ $followOrder->payment_method ?: '未設定' }}</p>
                                                </div>
                                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                                    <p class="text-xs text-slate-400">物流單號</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-700">{{ $followOrder->tracking_number ?: '待更新' }}</p>
                                                </div>
                                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                                    <p class="text-xs text-slate-400">總金額</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-700">{{ number_format((float) $followOrder->total_amount, 0) }} {{ $followOrder->currency }}</p>
                                                </div>
                                            </div>

                                            <section class="mt-4 rounded-xl border border-slate-200 p-4">
                                                <h5 class="text-sm font-bold text-slate-700">商品清單</h5>
                                                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                                                    @forelse($followOrder->items as $item)
                                                        <li class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                                                            <span class="truncate pr-4">{{ $item->name }}</span>
                                                            <span class="shrink-0 text-slate-500">× {{ $item->quantity }}</span>
                                                        </li>
                                                    @empty
                                                        <li class="rounded-lg bg-slate-50 px-3 py-2 text-slate-400">無商品資料</li>
                                                    @endforelse
                                                </ul>
                                            </section>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
