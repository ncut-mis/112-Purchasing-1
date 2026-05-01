<div class="bg-white rounded-2xl shadow-sm p-6">
    <div>
        <h3 class="text-lg font-bold text-gray-800">通知中心</h3>
        <p class="mt-1 text-sm text-gray-500">這裡顯示所有代購人的報價。您可以個別與他們溝通，並選擇最合適的報價。</p>
    </div>

    @php
        $countries = ['kr' => '韓國', 'jp' => '日本', 'us' => '美國', 'gb' => '英國'];
    @endphp

    <div class="space-y-4 mt-6">
        {{-- 注意：這裡迴圈跑的是 $offers，每個 $offer 代表一個人的報價 --}}
        @forelse($offers as $offer)
            @php
                $noti = $offer->requestList; // 關聯的需求清單
                $agent = $offer->user;        // 報價的代購人
            @endphp

            <div class="group relative rounded-2xl border border-gray-100 bg-white p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-4">
                        {{-- 代購人頭像/圖示 --}}
                        <div class="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-500 border border-indigo-100">
                            <i class="bi bi-person-badge text-xl"></i>
                        </div>
                        
                       <div>
    <div class="flex items-center gap-2">
        <h4 class="font-bold text-gray-800">{{ $offer->user->name ?? '未知代購人' }} 提交了報價</h4>
        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase">新報價</span>
    </div>
    
    <div class="text-sm text-gray-500 mt-1 flex flex-wrap items-start gap-1">
        <span class="shrink-0">需求項目：</span>
        <div class="flex flex-wrap gap-1">
            @forelse($offer->requestList->items as $item)
                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-medium border border-indigo-100">
                    {{ $item->name }} (x{{ $item->quantity }})
                </span>
                @if (!$loop->last)
                    <span class="text-gray-300 self-center">、</span>
                @endif
            @empty
                <span class="text-gray-400 italic">無商品資料</span>
            @endforelse
        </div>
    </div>
                        <div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <div class="flex items-center gap-1.5 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100 text-xs text-gray-600">
                                    <i class="bi bi-tag-fill text-amber-500"></i>
                                    報價：<span class="font-bold text-gray-900">
                                        {{ $offer->currency ?? 'TWD' }} 
                                        {{-- 確保這裡的欄位名稱跟資料庫一樣，如果是模型裡的 price 就改回 price --}}
                                        {{ number_format($offer->agent_quote_total ?? $offer->price ?? 0) }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-1.5 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100 text-xs text-gray-600">
                                    <i class="bi bi-truck text-blue-500"></i>
                                    預計時間：<span class="font-bold text-gray-900">{{ $offer->comment ?? '未約定' }}</span>
                                </div>

                                
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 w-full md:w-auto">
                        {{-- 聊聊按鈕 --}}
                        <button type="button" 
                                class="flex-1 md:flex-none px-4 py-2 bg-white border border-indigo-200 text-indigo-600 rounded-xl text-sm font-bold hover:bg-indigo-50 transition"
                                onclick="openRequestChatModal({{ $offer->id }})">
                            <i class="bi bi-chat-dots mr-1"></i> 與他聊聊
                        </button>

                        <button onclick="openRequestDetailModal({{ $offer->request_list_id }})" 
                                        class="flex items-center gap-1.5 px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg border border-indigo-100 text-xs font-bold hover:bg-indigo-100 transition">
                                    <i class="bi bi-eye"></i> 查看需求詳情
                        </button>

                        {{-- 接受按鈕 --}}
                        <form action="{{ route('quotes.accept', $offer->id) }}" method="POST" class="flex-1 md:flex-none">
                            @csrf
                            <button type="submit" onclick="return confirm('接受此報價將關閉其他人的報價，確定嗎？')"
                                    class="w-full px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-sm transition">
                                接受價格
                            </button>
                        
                        </form>

                        {{-- 拒絕按鈕 --}}
                        <form action="{{ route('quotes.reject', $offer->id) }}" method="POST" class="flex-1 md:flex-none">
                            @csrf
                            <button type="submit" onclick="return confirm('確定要拒絕此報價？')"
                                    class="w-full px-4 py-2 bg-white border border-red-100 text-red-500 rounded-xl text-sm font-bold hover:bg-red-50 transition">
                                拒絕
                            </button>
                        </form>

                    </div>
                </div>
            </div>

            {{-- 聊天 Modal (針對該 Offer) --}}
            <div id="request-chat-modal-{{ $offer->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
                <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <div class="px-5 py-4 border-b flex justify-between items-center">
                        <h4 class="font-bold text-lg">與 {{ $agent->name }} 對話中</h4>
                        <button onclick="closeRequestChatModal({{ $offer->id }})" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>
                    {{-- 訊息區 --}}
                    <div class="h-96 overflow-y-auto bg-gray-50 p-4" id="chat-messages-{{ $offer->id }}">
                        {{-- 訊息內容... --}}
                    </div>
                    {{-- 發送區 --}}
                    <form action="{{ route('request-list.chat.send', $noti->id) }}" method="POST" class="p-4 border-t flex gap-2">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $agent->id }}">
                        <input type="text" name="body" class="flex-1 border-gray-200 rounded-xl" placeholder="輸入訊息...">
                        <button class="px-6 py-2 bg-indigo-600 text-white rounded-xl font-bold">送出</button>
                    </form>
                </div>
            </div>

        @empty
            <div class="py-20 text-center border-2 border-dashed border-gray-100 rounded-3xl">
                <p class="text-gray-400">目前還沒有收到任何報價。</p>
            </div>
        @endforelse
    </div>
</div>

<script>
    function openRequestChatModal(id) {
        document.getElementById('request-chat-modal-' + id).classList.remove('hidden');
    }
    function closeRequestChatModal(id) {
        document.getElementById('request-chat-modal-' + id).classList.add('hidden');
    }
</script>