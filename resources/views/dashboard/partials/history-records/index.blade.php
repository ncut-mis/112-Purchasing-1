<div class="bg-white rounded-2xl shadow-sm p-6">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h3 class="text-lg font-bold text-gray-800">歷史紀錄</h3>
                <div class="inline-flex rounded-lg bg-slate-100 p-1">
                    <a href="{{ route('dashboard', ['section' => 'history-records', 'history_type' => 'request-lists']) }}" class="rounded-md px-3 py-1.5 text-sm font-semibold transition {{ $currentHistoryType === 'request-lists' ? 'bg-white text-slate-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">請託單</a>
                    <a href="{{ route('dashboard', ['section' => 'history-records', 'history_type' => 'follow-orders']) }}" class="rounded-md px-3 py-1.5 text-sm font-semibold transition {{ $currentHistoryType === 'follow-orders' ? 'bg-white text-slate-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">跟團紀錄</a>
                </div>
            </div>
            <p class="mt-1 text-sm text-gray-500">可切換查看「請託單」或「跟團紀錄」的完成歷史，避免資料混在一起。</p>
        </div>

        <div class="flex items-center gap-4">
           <form method="GET" action="{{ route('dashboard') }}" class="relative w-full md:w-96">
    <input type="hidden" name="section" value="history-records">
    <input type="hidden" name="history_type" value="{{ $currentHistoryType }}">

    <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#64748b] transition">
        <i class="bi bi-search"></i>
        </button>

    <input 
        type="search" 
        name="history_search" 
        placeholder="{{ $currentHistoryType === 'request-lists' ? '搜尋請購標題、狀態、代購人...' : '搜尋訂單標題、狀態、代購人...' }}" 
        value="{{ request('history_search') }}"
        class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#64748b] focus:border-[#64748b] shadow-sm transition outline-none"
    >
</form>
        </div>
    </div>

    @if(request('history_search'))
        <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            搜尋「{{ request('history_search') }}」找到 {{ $historyRecords->count() }} 筆歷史紀錄。
            <a href="{{ route('dashboard', ['section' => 'history-records', 'history_type' => $currentHistoryType]) }}" class="ml-2 font-semibold hover:underline">清除搜尋</a>
        </div>
    @endif

    <div class="space-y-2.5">
        @forelse($historyRecords as $historyRecord)
            @php
                $isRequestList = $historyRecord['type'] === 'request-list';
                $recordModel = $historyRecord['raw'];

                $statusText = match ($historyRecord['status']) {
                    'shipped' => '商品已出貨',
                    'arrivaled' => '商品已到貨',
                    'completed' => '已完成',
                    'expired' => '已過期',
                    'refunded' => '已退款',
                    default => $historyRecord['status'],
                };

                $statusStyle = match ($historyRecord['status']) {
                    'shipped' => 'bg-indigo-100 text-indigo-700',
                    'arrivaled' => 'bg-emerald-100 text-emerald-700',
                    'expired' => 'bg-rose-100 text-rose-700',
                    'refunded' => 'bg-amber-100 text-amber-700',
                    default => 'bg-slate-100 text-slate-700',
                };
            @endphp

            <article class="flex flex-col gap-2.5 rounded-[20px] border border-slate-200 bg-slate-50 px-3.5 py-3 shadow-sm lg:flex-row lg:items-center">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[18px] bg-white text-slate-500 shadow-sm">
                    @if($isRequestList)
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"></path>
                        </svg>
                    @else
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="truncate text-[0.92rem] font-semibold text-slate-700">{{ $historyRecord['title'] }}</h4>
                                <span class="inline-flex items-center rounded-full bg-slate-200 px-2 py-0.5 text-[0.72rem] font-semibold text-slate-600">
                                    {{ $isRequestList ? '請託單' : '跟團紀錄' }}
                                </span>
                            </div>

                            <div class="mt-1 flex flex-wrap items-center gap-x-6 gap-y-1 text-[0.82rem] text-slate-600">
                                <span>代購人：{{ $historyRecord['agent_name'] ?: '未指定代購人' }}</span>
                                <span>商品數量：{{ $historyRecord['item_count'] }} 件</span>
                                <span>總金額：{{ number_format((float) $historyRecord['amount'], 0) }} {{ $historyRecord['currency'] }}</span>
                            </div>

                            <div class="mt-1 flex flex-wrap items-center gap-x-5 gap-y-1 text-[0.82rem] text-slate-500">
                                <span>建立日期：{{ optional($historyRecord['created_at'])->format('Y-m-d') }}</span>
                                <span>更新日期：{{ optional($historyRecord['occurred_at'])->format('Y-m-d H:i') }}</span>
                                @if($historyRecord['country'])
                                    <span>地區：{{ $historyRecord['country'] }} {{ $historyRecord['city'] }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 lg:justify-end">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-[0.8rem] font-semibold {{ $statusStyle }}">
                                {{ $statusText }}
                            </span>
                            <button
                                type="button"
                                class="inline-flex items-center rounded-full bg-white px-3 py-1 text-[0.8rem] font-semibold text-slate-600 shadow-sm ring-1 ring-slate-300 transition hover:bg-slate-100"
                                onclick="openHistoryRecordModal('{{ $historyRecord['id'] }}')"
                            >
                                查看
                            </button>
                        </div>
                    </div>
                </div>
            </article>

            <div id="history-record-modal-{{ $historyRecord['id'] }}" class="hidden fixed inset-0 z-[72] flex items-center justify-center bg-slate-900/55 px-4 py-6" onclick="handleHistoryRecordBackdrop(event, '{{ $historyRecord['id'] }}')">
                <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">歷史紀錄明細</p>
                            <h4 class="mt-1 text-lg font-bold text-slate-800">{{ $historyRecord['title'] }}</h4>
                        </div>
                        <button type="button" class="rounded-full bg-white px-2.5 py-1 text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-50" onclick="closeHistoryRecordModal('{{ $historyRecord['id'] }}')" aria-label="關閉歷史紀錄視窗">✕</button>
                    </div>

                    <div class="max-h-[72vh] overflow-y-auto px-5 py-5">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-400">紀錄類型</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700">{{ $isRequestList ? '請託單' : '跟團紀錄' }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-400">狀態</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700">{{ $statusText }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-400">代購人</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700">{{ $historyRecord['agent_name'] ?: '未指定代購人' }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-400">總金額</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700">{{ number_format((float) $historyRecord['amount'], 0) }} {{ $historyRecord['currency'] }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-400">建立日期</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($historyRecord['created_at'])->format('Y-m-d H:i') ?? '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-xs text-slate-400">最後更新</p>
                                <p class="mt-1 text-sm font-semibold text-slate-700">{{ optional($historyRecord['occurred_at'])->format('Y-m-d H:i') ?? '-' }}</p>
                            </div>
                            @if($isRequestList)
                                <div class="rounded-xl bg-slate-50 px-4 py-3 sm:col-span-2">
                                    <p class="text-xs text-slate-400">地區與備註</p>
                                    <p class="mt-1 text-sm text-slate-700">
                                        {{ $historyRecord['country'] ?: '-' }} {{ $historyRecord['city'] ?: '' }}
                                        @if(!empty($recordModel->note))
                                            ｜ {{ $recordModel->note }}
                                        @endif
                                    </p>
                                </div>
                            @else
                                <div class="rounded-xl bg-slate-50 px-4 py-3 sm:col-span-2">
                                    <p class="text-xs text-slate-400">訂單資訊</p>
                                    <p class="mt-1 text-sm text-slate-700">
                                        訂單編號：{{ $recordModel->order_no }} ｜ 物流單號：{{ $recordModel->tracking_number ?: '待更新' }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <section class="mt-4 rounded-xl border border-slate-200 p-4">
                            <h5 class="text-sm font-bold text-slate-700">商品清單</h5>
                            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                                @if($isRequestList)
                                    @forelse($recordModel->items as $item)
                                        <li class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                                            <span class="truncate pr-4">{{ $item->product_name ?? $item->name ?? '未命名商品' }}</span>
                                            <span class="shrink-0 text-slate-500">× {{ $item->quantity }}</span>
                                        </li>
                                    @empty
                                        <li class="rounded-lg bg-slate-50 px-3 py-2 text-slate-400">無商品資料</li>
                                    @endforelse
                                @else
                                    @forelse($recordModel->items as $item)
                                        <li class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2">
                                            <span class="truncate pr-4">{{ $item->name }}</span>
                                            <span class="shrink-0 text-slate-500">× {{ $item->quantity }}</span>
                                        </li>
                                    @empty
                                        <li class="rounded-lg bg-slate-50 px-3 py-2 text-slate-400">無商品資料</li>
                                    @endforelse
                                @endif
                            </ul>
                        </section>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-sm text-gray-500">
                @if(request('history_search'))
                    找不到符合「{{ request('history_search') }}」的歷史紀錄。
                @else
                    目前尚無{{ $currentHistoryType === 'request-lists' ? '請託單' : '跟團紀錄' }}歷史紀錄，完成後會顯示在這裡。
                @endif
            </div>
        @endforelse
    </div>
</div>