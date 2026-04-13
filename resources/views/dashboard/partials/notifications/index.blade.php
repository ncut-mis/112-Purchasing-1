<div class="bg-white rounded-2xl shadow-sm p-6">
    <div>
        <h3 class="text-lg font-bold text-gray-800">通知 center</h3>
        <p class="mt-1 text-sm text-gray-500">這裡會顯示帶購人對您的請購單所提出的報價，您可以選擇接受或拒絕。</p>
    </div>
    @php
        $countries = [
            'kr' => '韓國', 'jp' => '日本', 
            'us' => '美國', 'gb' => '英國', 
        ];
    @endphp

    <div class="space-y-4 mt-6">
        @forelse($offeredRequests as $noti)
            {{-- 1. 通知卡片本體 --}}
            <div class="group relative rounded-2xl border border-gray-100 bg-white p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center text-amber-500 border border-amber-100">
                            <i class="bi bi-person-check-fill text-xl"></i>
                        </div>
                        
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-gray-800">有人想幫您代購！</h4>
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full uppercase tracking-wider">待處理報價</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                代購人：<span class="font-medium text-gray-700">{{ $noti->agent->name ?? '尚未指派' }}</span>
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <div class="flex items-center gap-1.5 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100 text-xs text-gray-600">
                                    <i class="bi bi-currency-dollar text-amber-600"></i>
                                    報價總額：<span class="font-bold text-gray-900">{{ $noti->currency }} {{ number_format($noti->agent_quote_total) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100 text-xs text-gray-600">
                                    <i class="bi bi-clock text-blue-500"></i>
                                    預計時間：<span class="font-bold text-gray-900">{{ $noti->time ?? '未提供時間' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 w-full md:w-auto">
                        <button type="button" 
                                class="inline-flex items-center rounded-lg bg-yellow-500 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-yellow-600" 
                                onclick="openDetail({{ $noti->id }})">
                            詳細內容
                        </button>
                        @if(!empty($noti->people))
                            <button type="button"
                                    class="inline-flex items-center rounded-lg bg-emerald-500 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-600"
                                    onclick="openRequestChatModal({{ $noti->id }})">
                                聊一聊
                            </button>
                        @else
                            <button type="button"
                                    class="inline-flex items-center rounded-lg bg-gray-300 px-4 py-2 text-xs font-semibold text-white cursor-not-allowed"
                                    title="目前尚未有已接單代購人，暫時無法聊天"
                                    disabled>
                                聊一聊
                            </button>
                        @endif
                        <button class="flex-1 md:flex-none px-6 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-xl text-sm font-bold shadow-sm shadow-green-200 transition-all active:scale-95">
                            接受代購
                        </button>
                        {{-- 修正：$quote->id 應改為 $noti->id 以符合迴圈變數 --}}
                        <form action="{{ route('request.chat.reject', $noti->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="flex-1 md:flex-none px-6 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-xl text-sm font-bold shadow-sm transition-all active:scale-95">
                                拒絕
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- 2. 檢視內容 Modal --}}
            <div id="request-detail-modal-{{ $noti->id }}" 
                 class="request-detail-modal hidden fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 px-4 py-4" 
                 onclick="if(event.target === this) closeDetail({{ $noti->id }})">
                
                 <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl" onclick="event.stopPropagation()">
                    {{-- Modal Header --}}
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 bg-gradient-to-r from-amber-600 to-yellow-500 px-5 py-4 text-white">
                        <div>
                            <p class="text-sm font-medium text-amber-100">詳細內容</p>
                            <h4 class="mt-1 text-2xl font-bold">{{ $noti->title }}</h4>
                            <p class="text-sm font-medium text-amber-100">以下是詳細商品明細,請確認詳細內容再決定是否接受此代購人幫您代購</p>
                        </div>
                        <button type="button" class="rounded-full bg-white/15 p-2 text-white transition hover:bg-white/30" onclick="closeDetail({{ $noti->id }})">✕</button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="max-h-[72vh] overflow-y-auto px-5 py-5 text-left">
                        {{-- 商家資訊 --}}
                        <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                            <h5 class="font-bold text-slate-800 text-lg mb-4">商家與購買資訊</h5>
                            <dl class="space-y-3 text-base">
                                <div class="flex justify-between items-center border-b border-slate-50 pb-2">
                                    <dt class="text-slate-500 font-medium">國家</dt>
                                    <dd class="font-bold text-slate-800">{{ $countries[$noti->country] ?? $noti->country ?? '未提供' }}</dd>
                                </div>
                                <div class="flex justify-between items-center border-b border-slate-50 pb-2">
                                    <dt class="text-slate-500 font-medium">店家名稱</dt>
                                    <dd class="font-bold text-slate-800">{{ $noti->title ?: '未提供' }}</dd>
                                </div>
                                <div class="pt-2">
                                    <dt class="text-slate-500 block mb-2 font-medium">詳細地址</dt>
                                    <dd class="rounded-xl bg-slate-50 p-3 text-slate-700 text-sm leading-relaxed break-words">
                                        {{ $noti->detail_address ?: '未提供詳細地址' }}
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        {{-- 單價卡片區塊 --}}
                        <div class="mt-6">
                            @if($noti->items->first() && !is_null($noti->items->first()->expected_price))
                                <section class="rounded-3xl border border-blue-100 bg-blue-50/20 p-6 shadow-sm ring-1 ring-blue-50">
                                    <h5 class="text-sm font-bold text-blue-600 mb-4 uppercase tracking-wider">代購人填寫的商品單價</h5>
                                    <div class="grid grid-cols-1 gap-3">
                                        @foreach($noti->items as $item)
                                            <div class="flex items-center justify-between gap-4 p-4 rounded-xl bg-white border border-blue-100/50 shadow-sm">
                                                <span class="text-base font-bold text-slate-800 flex-1 leading-snug whitespace-normal">
                                                    {{ $item->name }}
                                                </span>
                                                <div class="text-right shrink-0 border-l border-slate-100 pl-4">
                                                    <span class="text-xs font-black text-slate-400 block uppercase">{{ $noti->currency }}</span>
                                                    <span class="text-xl font-black text-blue-700">
                                                        {{ number_format((float) ($item->expected_price ?? 0), 0) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @else
                                <div class="text-center py-10 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200">
                                    <p class="text-slate-400 font-medium text-lg">目前的報價已拒絕</p>
                                    <p class="text-sm text-slate-400 mt-2">正在等待其他代購人重新提供價格...</p>
                                </div>
                            @endif
                        </div>

                        {{-- 商品清單表格 --}}
                        <div class="mt-8">
                            <h5 class="font-bold text-slate-800 mb-4 text-base">商品清單</h5>
                            <div class="overflow-hidden rounded-xl border border-slate-200">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="bg-slate-50 border-b border-slate-200 text-left text-slate-500">
                                            <th class="px-4 py-3 font-medium">商品圖片</th>
                                            <th class="px-4 py-3 font-medium">商品名稱</th>
                                            <th class="px-4 py-3 font-medium text-center">需求量</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        @foreach($noti->items as $item)
                                            <tr class="border-b border-slate-100 last:border-0">
                                                <td class="px-4 py-3">
                                                    <div class="h-16 w-16 overflow-hidden rounded-lg bg-slate-100">
                                                        @if($item->reference_image)
                                                            <img src="{{ url('/request-item-image/' . $item->id) }}" class="h-full w-full object-cover">
                                                        @else
                                                            <div class="flex h-full items-center justify-center text-[10px] text-slate-400">無圖片</div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 font-medium text-slate-800">{{ $item->name }}</td>
                                                <td class="px-4 py-3 text-center text-slate-700 font-bold">{{ $item->quantity }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> {{-- End Modal Body --}}
                </div> {{-- End Modal Content --}}
            </div> {{-- End Modal Wrapper --}}


            @if(!empty($noti->people))
                @php
                    $chatMessages = \App\Models\Message::query()
                        ->where('request_list_id', $noti->id)
                        ->where(function ($query) use ($noti) {
                            $query->where(function ($inner) use ($noti) {
                                $inner->where('sender_id', $noti->user_id)
                                    ->where('receiver_id', $noti->people);
                            })->orWhere(function ($inner) use ($noti) {
                                $inner->where('sender_id', $noti->people)
                                    ->where('receiver_id', $noti->user_id);
                            });
                        })
                        ->with(['sender:id,name'])
                        ->orderBy('created_at')
                        ->get();
                @endphp
                <div id="request-chat-modal-{{ $noti->id }}" class="request-chat-modal hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-4" onclick="handleRequestChatBackdrop(event, {{ $noti->id }})">
                    <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                            <div>
                                <p class="text-xs text-slate-500">請購單 #{{ $noti->id }}</p>
                                <h4 class="text-lg font-bold text-slate-800">{{ $noti->title }}</h4>
                            </div>
                            <button type="button" class="text-slate-500 text-2xl leading-none hover:text-slate-700" onclick="closeRequestChatModal({{ $noti->id }})" aria-label="關閉聊天室">✕</button>
                        </div>

                        <div id="request-chat-messages-{{ $noti->id }}" class="max-h-[55vh] overflow-y-auto bg-slate-50 px-5 py-4">
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
                              action="{{ route('request-list.chat.store', $noti) }}"
                              class="request-chat-form flex items-center gap-2 border-t border-slate-200 px-4 py-3"
                              data-request-list-id="{{ $noti->id }}">
                            @csrf
                            <input type="text" name="body" class="w-full rounded-full border-slate-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="輸入訊息..." maxlength="2000" required>
                            <button type="submit" class="rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">送出</button>
                        </form>
                    </div>
                </div>
            @endif
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/50 px-6 py-16 text-center">
                <i class="bi bi-bell-slash text-2xl text-gray-300"></i>
                <p class="text-gray-400 font-medium mt-4">目前尚無代購人報價。</p>
            </div>
        @endforelse
    </div>
</div>

<script>
    window.openDetail = function(id) {
        const modal = document.getElementById('request-detail-modal-' + id);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeDetail = function(id) {
        const modal = document.getElementById('request-detail-modal-' + id);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    };
</script>