<x-app-layout>
    @php
        $keyword = $keyword ?? request('q', '');
        $selectedCountry = $selectedCountry ?? request('country', 'all');
        $selectedTime = $selectedTime ?? request('time', 'all');
        // 確保分頁物件存在，避免報錯
        $requestLists = $requestLists ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        
        $countryLabels = [
            'all' => '全部地區',
            'jp' => '🇯🇵 日本',
            'kr' => '🇰🇷 韓國',
            'us' => '🇺🇸 美國',
            'gb' => '🇬🇧 英國',
        ];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-indigo-800 leading-tight">
                {{ __('代購接單大廳') }}
            </h2>

            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('agent.dashboard') }}" class="relative w-full md:w-96 group">
                    <input type="text" name="q" value="{{ $keyword }}"
                        class="w-full pl-5 pr-12 py-3 bg-white border-2 border-indigo-50 rounded-2xl text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-md transition-all duration-300 group-hover:border-indigo-200"
                        placeholder="搜尋商品名稱、國家或備註關鍵字...">
                    @if($selectedCountry !== 'all') <input type="hidden" name="country" value="{{ $selectedCountry }}"> @endif
                    @if($selectedTime !== 'all') <input type="hidden" name="time" value="{{ $selectedTime }}"> @endif
                    <div class="absolute inset-y-0 right-0 flex items-center pr-1">
                        <button type="submit" class="bg-indigo-600 text-white p-2 rounded-xl hover:bg-indigo-700 transition shadow-sm">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>

                <a href="{{ route('agent.member') }}" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent rounded-full text-sm font-bold text-white hover:bg-indigo-700 transition shadow-md">
                    <i class="bi bi-person-badge"></i>
                    <span>會員專區</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Alpine.js 狀態中心 -->
<div x-data="{ 
    showDetailModal: false, 
    showPriceModal: false,
    selectedRequest: null,
    agentQuoteTotal: '',
    availableTime: '',
    loading: false,

    get totalQuote() {
            if (!this.selectedRequest || !this.selectedRequest.items) return 0;
            return this.selectedRequest.items.reduce((sum, item) => {
                return sum + (parseFloat(item.agent_quote) || 0);
            }, 0);
        },


    
    openDetail(data) {
    console.log('原始收到的資料:', data);
    const rawData = JSON.parse(JSON.stringify(data));

    // 1. 加工資料：幫每個商品(item) 建立一個專屬於它的報價欄位
    // 我們使用 .map() 遍歷陣列，並回傳一個「升級版」的陣列
    const itemsWithPrivateFields = data.items.map(item => {
        return {
            ...item,            // 展開運算子：保留原本的 id, name, quantity 等
            agent_quote: '',    // ✅ 新增屬性：這就是該商品的「獨立報價大腦」
        };
    });

    // 2. 將整包資料（含加工後的 items）存入 Alpine.js 的變數中
    this.selectedRequest = {
        ...data,                // 保留原本的 request_list 資訊 (id, title 等)
        items: itemsWithPrivateFields // 替換成我們加工過的 items
    };

    // 3. UI 控制：開啟彈窗並禁止背景滾動
    this.showDetailModal = true;
    document.body.style.overflow = 'hidden';
},
    
    goToQuote() {
        this.showDetailModal = false;
        setTimeout(() => { this.showPriceModal = true; }, 150);
    },
    closeAll() {
        this.showDetailModal = false;
        this.showPriceModal = false;
        document.body.style.overflow = 'auto';
        this.agentQuoteTotal = '';
        this.availableTime = '';
    },
submitQuote() {
    // 1. 計算所有商品的總額 (將每個 item 的 agent_quote 加起來)
    const total = this.selectedRequest.items.reduce((sum, item) => {
        return sum + (parseFloat(item.agent_quote) || 0);
    }, 0);
    
    // 2. 驗證：檢查總額是否大於 0 以及是否有填寫時段
    if (total <= 0 || !this.availableTime.trim()) {
        alert('請針對商品填寫報價與可代購時段');
        return;
    }
    
    // 3. 準備 Payload
    const payload = {
        id: this.selectedRequest.id,
        agent_quote_total: total,        // 這是算出來的總分
        time: this.availableTime.trim(),
        // 把包含個別報價的 items 陣列也送過去
        items: this.selectedRequest.items.map(item => ({
            id: item.id,
            agent_quote: item.agent_quote
        }))
    };
    
    console.log('準備送出報價:', payload);
    
    // 4. 執行 Fetch
    fetch('/request-lists/agent-quote', { 
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload) 
    })
    .then(async response => {
        const data = await response.json();
        if (response.ok) {
            alert('報價送出成功！');
            this.closeAll();
            window.location.reload(); 
        } else {
            throw new Error(data.message || '送出失敗');
        }
    })
    .catch(error => {
        console.error('送出失敗:', error);
        alert('送出失敗：' + error.message);
    });
}
}" @keydown.escape.window="closeAll()" class="py-12 bg-gray-50 min-h-screen">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- 篩選區 (保持原樣) -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8 space-y-6">
                <div>
                    <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="bi bi-geo-alt"></i> 依國家篩選
                    </h5>
                    <div class="flex flex-wrap gap-2">
                        @foreach($countryLabels as $code => $label)
                            <a href="{{ route('agent.dashboard', array_filter(['country' => $code === 'all' ? null : $code, 'time' => $selectedTime !== 'all' ? $selectedTime : null, 'q' => $keyword ?: null])) }}"
                               class="px-5 py-2 rounded-full text-sm font-bold transition {{ $selectedCountry === $code ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-50 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-50">
                    <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="bi bi-clock-history"></i> 依時間篩選
                    </h5>
                    @php
                        $timeOptions = ['all' => '不限時間', 'urgent' => '最緊急 (24H內)', 'three_days' => '3天內截止', 'this_week' => '本周截止'];
                    @endphp
                    <div class="flex flex-wrap gap-2">
                        @foreach($timeOptions as $timeKey => $timeLabel)
                            <a href="{{ route('agent.dashboard', array_filter(['country' => $selectedCountry !== 'all' ? $selectedCountry : null, 'time' => $timeKey === 'all' ? null : $timeKey, 'q' => $keyword ?: null])) }}"
                               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $selectedTime === $timeKey ? 'bg-indigo-600 text-white shadow-md' : 'bg-white border border-gray-200 text-gray-600 hover:border-indigo-500 hover:text-indigo-600' }}">
                                {{ $timeLabel }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800">最新請購需求</h3>
                    <span class="text-sm text-gray-400">找到 {{ $requestLists->total() }} 個符合條件的請購</span>
                </div>

                <!-- 請購單列表：保留您原本的卡片架構 -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    @forelse($requestLists as $requestList)
        @php
            // 1. 整理基礎資料
            $countryCode = $requestList->country;
            $countryTag = $countryLabels[$countryCode] ?? $countryCode;
            $firstItem = $requestList->items->first();
            $title = $requestList->title ?: ($firstItem->name ?? '未命名請購');

            // 2. 權限與狀態判定
            $isOwner = auth()->check() && (int) $requestList->user_id === (int) auth()->id();
            $isFavorited = in_array((int) $requestList->id, $favoritedRequestListIds ?? [], true);
            
            // 3. 準備傳給 Alpine.js 的 JSON 資料 (包含圖片與 ID)
            $orderData = [
                'id' => $requestList->id,
                'title' => $title,
                'address' => $requestList->detail_address ?: '未填寫',
                'deadline' => optional($requestList->deadline)->format('Y-m-d') ?: '不限時',
                'note' => $requestList->note ?: '-',
                'items' => $requestList->items->map(fn($i) => [
                    'id' => $i->id,
                    'name' => $i->name, 
                    'quantity' => $i->quantity,
                    'image' => $i->reference_image ? url('/request-item-image/' . $i->id) : null
                ])
            ];
        @endphp

        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:border-indigo-200 hover:shadow-md transition group flex flex-col h-full">
            
            <div class="flex justify-between items-start mb-4 gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-inner shrink-0">
                        <i class="bi bi-bag-heart-fill text-xl"></i>
                    </div>
                    <div class="min-w-0">
                        <h4 class="font-bold text-gray-800 group-hover:text-indigo-600 transition truncate">{{ $title }}</h4>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span class="text-[10px] px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md font-bold">{{ $countryTag }}</span>
                            <span class="text-[10px] px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md font-bold">截止：{{ $orderData['deadline'] }}</span>
                        </div>
                    </div>
                </div>

                <button type="button" 
                    class="favorite-toggle w-9 h-9 rounded-full transition flex items-center justify-center {{ $isFavorited ? 'bg-pink-50 text-pink-500' : 'bg-gray-100 text-gray-400 hover:bg-pink-50 hover:text-pink-400' }}"
                    data-request-list-id="{{ $requestList->id }}"
                    @if($isOwner) disabled title="不能收藏自己的請購清單" style="opacity: 0.5; cursor: not-allowed;" @endif>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12.001 4.529c2.349-2.532 6.15-2.533 8.498-.001 2.41 2.6 2.41 6.815 0 9.416l-7.66 8.266a1.14 1.14 0 0 1-1.677 0l-7.66-8.266c-2.41-2.601-2.41-6.817 0-9.416 2.348-2.532 6.149-2.531 8.499.001Z"/></svg>
                </button>
            </div>

            <div class="mb-5 flex-1">
                <p class="text-xs font-bold text-gray-400 mb-2 uppercase tracking-wider">請購內容</p>
                <ul class="space-y-1.5 text-sm text-gray-600">
                    @foreach($requestList->items as $item)
                        <li class="flex items-start gap-1">
                            <span class="text-indigo-300">•</span>
                            <span>{{ $item->name }} <span class="text-gray-400 text-xs">× {{ $item->quantity }}</span></span>
                        </li>
                    @endforeach
                </ul>

                @if($requestList->detail_address)
                    <div class="mt-4 pt-4 border-t border-gray-50">
                        <p class="text-xs text-gray-500 leading-relaxed line-clamp-2">
                            <span class="font-bold text-gray-400">送貨地址：</span>{{ $requestList->detail_address }}
                        </p>
                    </div>
                @endif 
            </div>

            <div class="pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    {{-- 左側：請購人資訊 --}}
                    <div class="flex items-center gap-2 min-w-0">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($requestList->user->name ?? 'User') }}&background=EEF2FF&color=4F46E5" class="w-7 h-7 rounded-full border border-gray-100" alt="avatar">
                        <div class="flex flex-col min-w-0">
                            <span class="text-[10px] text-gray-400 leading-none">請購人</span>
                            <span class="text-xs text-gray-700 font-medium truncate">{{ $requestList->user->name ?? '未知' }}</span>
                        </div>
                    </div>

                    {{-- 右側：按鈕邏輯 --}}
                    <div class="flex items-center">
                        @if($isOwner)
                            {{-- 1. 自己的單 --}}
                            <span class="text-[10px] px-2 py-1 bg-red-50 text-red-400 rounded-lg font-bold">本人清單</span>
                        @elseif($requestList->people)
                            {{-- 2. 已經有人承接 --}}
                            @if((int)$requestList->people === (int)auth()->id())
                                <a href="{{ route('messages.index', ['id' => $requestList->id]) }}" 
                                   class="px-4 py-2 bg-green-500 text-white rounded-xl text-xs font-bold hover:bg-green-600 transition shadow-sm">
                                    聊一聊
                                </a>
                            @else
                                <span class="text-[10px] px-2 py-1 bg-gray-100 text-gray-400 rounded-lg font-bold">已被承接</span>
                            @endif
                        @else
                            {{-- 3. 開放接單：呼叫 JS 函數處理獨立報價 --}}
                            <button type="button" @click="openDetail(@js($orderData))"
                                    class="px-5 py-2 bg-indigo-600 text-white rounded-xl font-bold text-xs hover:bg-indigo-700 transition shadow-md shadow-indigo-100 active:scale-95 flex items-center gap-1">
                                <i class="bi bi-cart-plus"></i> 查看詳情
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div> @empty
        <div class="col-span-full text-center py-20 bg-white rounded-[2rem] border-2 border-dashed border-gray-100">
            <div class="text-gray-300 mb-2"><i class="bi bi-inbox text-5xl"></i></div>
            <p class="text-gray-400 font-bold text-lg">目前沒有符合條件的請購需求</p>
        </div>
    @endforelse
</div>


            <!-- [彈窗 1] 詳細資料 Modal -->
                    
            <div x-show="showDetailModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
                <div class="absolute inset-0 bg-black/30" @click="closeAll()"></div>
                <div x-show="showDetailModal" x-transition class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-2xl text-left relative">
                    <button @click="closeAll()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-indigo-700 mb-4" x-text="selectedRequest?.title"></h3>
                        <div class="space-y-1">
                            <div class="text-sm text-gray-500"><span class="font-bold">地址：</span><span x-text="selectedRequest?.address"></span></div>
                            <div class="text-sm text-gray-500"><span class="font-bold">截止日期：</span><span x-text="selectedRequest?.deadline"></span></div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="font-bold text-gray-700 mb-3">商品明細</div>
                        <div class="space-y-3 max-h-[30vh] overflow-y-auto pr-2 custom-scrollbar">
                            <template x-for="item in selectedRequest?.items">
                                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
                                    <div class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center overflow-hidden">
                                        <template x-if="item.image">
                                            <img :src="item.image" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!item.image">
                                            <i class="bi bi-image text-gray-400"></i>
                                        </template>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-800" x-text="item.name"></div>
                                        <div class="text-xs text-gray-500" x-text="'數量：' + item.quantity"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mb-8">
                        <div class="font-bold text-gray-700 mb-1">備註</div>
                        <div class="text-gray-600 text-sm bg-gray-50 p-4 rounded-xl italic" x-text="selectedRequest?.note"></div>
                    </div>

                    <div class="flex justify-end">
                        <button @click="goToQuote()" class="px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition">確定接單</button>
                    </div>
                </div>
            </div>

            <!-- [彈窗 2] 報價單 Modal -->
            <div x-show="showPriceModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4" x-cloak>
                <div class="absolute inset-0 bg-indigo-950/40 backdrop-blur-sm" @click="closeAll()"></div>
                <div x-show="showPriceModal" x-transition class="bg-white rounded-3xl shadow-2xl overflow-hidden w-full max-w-2xl relative flex flex-col max-h-[90vh]">
                    <div class="h-2 bg-indigo-600"></div>
                    
                    <div class="p-8 pb-4">
                        <h4 class="text-2xl font-black text-gray-800 mb-2">填寫代購報價單</h4>
                        <p class="text-gray-400 text-sm">請針對請購單內的每一項商品提供報價 (NTD)</p>
                    </div>

                    <!-- 商品報價列表 (可滾動區) -->
                    <div class="flex-1 overflow-y-auto px-8 custom-scrollbar space-y-6">
                        <template x-for="(item, index) in selectedRequest?.items" :key="index">
                            <div class="p-5 bg-indigo-50/30 rounded-3xl border-2 border-indigo-50 hover:border-indigo-100 transition shadow-sm">
                                <div class="flex items-center gap-4 mb-5">
                                    <div class="w-20 h-20 rounded-2xl bg-white flex items-center justify-center overflow-hidden border-2 border-white shadow-sm shrink-0">
                                        <template x-if="item.image">
                                            <img :src="item.image" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!item.image">
                                            <i class="bi bi-image text-gray-300 text-xl"></i>
                                        </template>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-black text-gray-800 text-base mb-1" x-text="item.name"></div>
                                        <div class="inline-flex items-center px-3 py-1 bg-white rounded-full text-xs text-indigo-600 font-black shadow-sm border border-indigo-50" x-text="'需求數量：' + item.quantity"></div>
                                    </div>
                                </div>
                                
                                <div class="relative group">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-base font-black text-indigo-400">NT$</span>
                                    <input type="number" 
                                        x-model="item.agent_quote"
                                        class="w-full pl-16 pr-6 py-4 bg-white border-2 border-indigo-100 rounded-2xl text-xl font-black text-indigo-600 focus:border-indigo-500 shadow-sm transition-all" 
                                        placeholder="輸入此訂單之總報價">
                                </div>
                            </div>
                        </template>

                        <div class="pt-2 pb-6">
                            <label class="block text-xs font-black text-gray-500 uppercase mb-3 tracking-widest flex items-center gap-2">
                                <i class="bi bi-calendar-event text-indigo-500"></i> 請輸入可代購時段
                            </label>
                            <textarea x-model="availableTime" rows="3" 
                                class="w-full p-6 bg-white border-2 border-indigo-100 rounded-3xl text-sm font-bold text-gray-700 placeholder-gray-300 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 shadow-sm transition-all" 
                                placeholder="例如：本週末於實體門市採買，預計下週一完成空運寄出"></textarea>
                        </div>
                    </div>

                    <!-- 底部操作按鈕區 -->
                    <!-- 報價 Modal 底部 -->
                        <div class="p-8 pt-6 border-t bg-gray-50">
                            <div class="flex gap-4">
                                <!-- 取消 -->
                                <button @click="closeAll()" 
                                        class="flex-1 py-4 text-gray-500 font-black hover:text-gray-700">
                                    取消
                                </button>
                                <button @click="submitQuote()"
    :disabled="totalQuote <= 0 || !availableTime.trim()"
    :class="(totalQuote <= 0 || !availableTime.trim()) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-indigo-700'"
    class="flex-1 py-4 bg-indigo-600 text-white rounded-2xl font-black transition-all">
    
    確認送出 (NT$ <span x-text="totalQuote"></span>)
</button>
                            </div>
                        </div>
                </div>
            </div>
        </div>


    <style>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    </style>

    <script>
        // 保留原本的收藏邏輯
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.favorite-toggle').forEach(button => {
                button.addEventListener('click', function () {
                    if (this.disabled) {
                        return;
                    }
                    const id = this.getAttribute('data-request-list-id');
                    fetch("{{ route('favorite.toggle') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ type: 'request_list', id: id })
                    })
                    .then(async res => {
                        const data = await res.json();
                        if (!res.ok) {
                            throw new Error(data.message || '收藏操作失敗');
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.status === 'added') {
                            this.classList.add('text-pink-500', 'bg-pink-50');
                            this.classList.remove('text-gray-400', 'bg-gray-100');
                        } else {
                            this.classList.remove('text-pink-500', 'bg-pink-50');
                            this.classList.add('text-gray-400', 'bg-gray-100');
                        }
   })
                    .catch(error => {
                        alert(error.message || '收藏操作失敗');
                    });
                });
            });
        });
    </script>
</x-app-layout>