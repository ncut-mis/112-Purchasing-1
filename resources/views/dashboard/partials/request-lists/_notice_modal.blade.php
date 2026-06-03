<div id="request-notice-modal-{{ $requestList->id }}"
    class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 px-4 py-6"
    onclick="handleRequestNoticeBackdrop(event, {{ $requestList->id }})">

    <div class="w-full max-w-4xl rounded-3xl bg-white shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5 bg-slate-50/50">
            <h4 class="text-xl font-extrabold text-slate-800 flex items-center">
                <i class="bi bi-bell-fill text-amber-500 mr-2"></i>通知中心
            </h4>
            <button type="button" class="rounded-full bg-white border border-slate-200 p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition shadow-sm" onclick="closeRequestNoticeModal({{ $requestList->id }})">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="px-6 py-6 text-base leading-relaxed text-slate-700">
            @if($requestList->status === 'offered')
                <div class="mb-6">
                    <p class="text-lg text-slate-700">收到以下代購人的報價：</p>
                    <p class="text-sm text-slate-400">您可以點擊查看詳細資訊後再決定是否接受。</p>
                </div>

                <div class="space-y-4 pr-2 custom-scrollbar" style="max-height: 450px; overflow-y: auto;">
                    @forelse($requestList->quotes->where('status', 'pending') as $quote)
                        @php
                            $quoteItemPrices = method_exists($quote, 'quoteItems')
                                ? $quote->quoteItems->keyBy('request_item_id')
                                : collect();
                            $unreadChatCount = \App\Models\Message::query()
                                ->where('request_list_id', $requestList->id)
                                ->where('sender_id', $quote->user_id)
                                ->where('receiver_id', $requestList->user_id)
                                ->whereNull('read_at')
                                ->count();
                        @endphp
                        <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center space-x-5">
                                <div class="h-14 w-14 flex-shrink-0 rounded-full bg-amber-50 flex items-center justify-center border-2 border-white shadow-sm ring-1 ring-slate-100">
                                    @if($quote->user->avatar)
                                        <img src="{{ asset('storage/' . $quote->user->avatar) }}" class="h-full w-full rounded-full object-cover">
                                    @else
                                        <i class="bi bi-person-fill text-2xl text-amber-300"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-slate-900 group-hover:text-amber-600 transition-colors">{{ $quote->user->name }}</div>
                                    <div class="text-sm text-slate-500 mt-1">
                                        報價總額：<span class="text-green-600 font-extrabold text-lg">NT$ {{ number_format($quote->price) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <button type="button"
                                    onclick="event.stopPropagation(); openRequestChatModal({{ $requestList->id }}, {{ $quote->user->id }})"
                                    class="relative rounded-xl px-4 py-2 text-sm font-bold bg-green-50 text-green-600 border border-green-200 hover:bg-green-500 hover:text-white transition-all shadow-sm">
                                    聊天
                                    <span class="notice-chat-badge {{ $unreadChatCount > 0 ? '' : 'hidden' }} absolute -top-2 -right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] leading-[18px] text-center font-bold"
                                          data-request-list-id="{{ $requestList->id }}"
                                          data-agent-id="{{ $quote->user_id }}">{{ $unreadChatCount > 0 ? $unreadChatCount : '' }}</span>
                                </button>
                                <button type="button" onclick="openQuoteDetailModal({{ $quote->id }})" class="rounded-xl px-4 py-2 text-sm font-bold bg-amber-50 text-amber-600 border border-amber-200 hover:bg-amber-500 hover:text-white transition-all shadow-sm">查看詳細</button>

                                <form action="{{ route('quotes.accept', $quote->id) }}" method="POST" onsubmit="return confirm('確定要接受此報價嗎？')">
                                    @csrf
                                    <button type="submit" class="rounded-xl px-4 py-2 text-sm font-bold bg-green-600 text-white hover:bg-green-700 shadow-md shadow-green-100 transition-all">接受</button>
                                </form>
                                <form action="{{ route('quotes.return', $quote->id) }}" method="POST" onsubmit="return confirm('確定要退回此報價並請代購人修改嗎？')">
                                    @csrf
                                    <button type="submit" class="rounded-xl px-4 py-2 text-sm font-bold bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-500 hover:text-white transition-all shadow-sm">退回修改</button>
                                </form>
                                <form action="{{ route('quotes.reject', $quote->id) }}" method="POST" onsubmit="return confirm('確定要拒絕此報價嗎？')">
                                    @csrf
                                    <button type="submit" class="rounded-xl px-4 py-2 text-sm font-bold bg-white text-red-500 border border-red-100 hover:bg-red-50 transition-all">拒絕</button>
                                </form>
                            </div>
                        </div>

                        <div id="quote-detail-modal-{{ $quote->id }}" class="hidden fixed inset-0 z-[75] bg-slate-950/70 backdrop-blur-sm px-4 py-6" onclick="handleQuoteDetailBackdrop(event, {{ $quote->id }})">
                            <div class="mx-auto w-full max-w-4xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                                <div class="flex items-center justify-between border-b border-slate-200 bg-amber-100 px-6 py-4">
                                    <h5 class="text-lg font-black text-slate-800">報價詳細內容【代購人:{{ $quote->user->name }}】</h5>
                                    <button type="button" class="rounded-full border border-slate-200 bg-white p-2 text-slate-400 hover:text-slate-600" onclick="closeQuoteDetailModal({{ $quote->id }})"><i class="bi bi-x-lg"></i></button>
                                </div>

                                <div class="max-h-[75vh] overflow-y-auto px-6 py-5">
                                    <div class="mb-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm">
                                        <span class="font-semibold text-slate-700">報價備註：</span>
                                        <span class="text-slate-600">{{ $quote->comment ?: '未提供' }}</span>
                                    </div>

                                    <div class="space-y-4">
                                        @foreach($requestList->items as $item)
                                            @php
                                                $quotedUnitPrice = $quoteItemPrices->get($item->id)?->unit_price;
                                                $unitPrice = $quotedUnitPrice ?? $item->expected_price ?? 0;
                                                $quantity = max((int) ($item->quantity ?? 1), 1);
                                            @endphp
                                            <div class="grid grid-cols-12 gap-4 rounded-2xl border border-slate-200 p-4">
                                                <div class="col-span-12 md:col-span-2">
                                                    @if($item->reference_image)
                                                       <div class="flex h-24 w-full items-center justify-center overflow-hidden rounded-xl bg-slate-100">
                                                            <img src="{{ route('request-item.image', ['requestList' => $requestList->id, 'requestItem' => $item->id, 'v' => $item->updated_at?->timestamp ?? now()->timestamp]) }}" alt="{{ $item->name }}" class="h-full w-full object-contain">
                                                        </div>
                                                    @else
                                                        <div class="flex h-24 w-full items-center justify-center rounded-xl bg-slate-100 text-slate-400"><i class="bi bi-image"></i></div>
                                                    @endif
                                                </div>
                                                <div class="col-span-12 md:col-span-10">
                                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                                        <div>
                                                            <p class="text-base font-bold text-slate-800">{{ $item->name ?? '未命名商品' }}</p>
                                                            <p class="mt-1 text-sm text-slate-500">數量：{{ $quantity }}</p>
                                                        </div>
                                                        <div class="text-right">
                                                            <p class="text-sm text-slate-500">單價</p>
                                                            <p class="text-lg font-extrabold text-emerald-600">NT$ {{ number_format($unitPrice, 0) }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-right">
                                        <p class="text-sm text-slate-500">總計金額</p>
                                        <p class="text-3xl font-black text-amber-600">NT$ {{ number_format($quote->price) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 rounded-3xl border-2 border-dashed border-slate-100 text-slate-300">
                            <i class="bi bi-inbox text-5xl mb-3 block"></i>
                            <p class="text-lg">目前尚無待處理報價</p>
                        </div>
                    @endforelse
                </div>
            @else
                <div class="text-center py-10">
                    <p class="text-lg text-slate-600 mb-6">這裡會顯示此請託單有哪些代購人報價，您可以<span class="font-semibold text-green-600">接受</span>、<span class="font-semibold text-red-600">拒絕</span>、<span class="font-semibold text-amber-600">查看詳細內容</span>。</p>
                    <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 px-6 py-10">
                        <div class="animate-pulse flex flex-col items-center">
                            <i class="bi bi-hourglass-split text-4xl text-slate-300 mb-3"></i>
                            <p class="text-slate-400 font-medium">此請託單尚未有代購人報價，請耐心等候...</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>