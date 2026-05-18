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
                    <span>代購人專區</span>
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Alpine.js 狀態中心 -->

    <div x-data="{
        showDetailModal: false,
        showPriceModal: false,
        selectedRequest: null,
        availableTime: '',
        quoteRemarks: '',
        loading: false,

        get totalQuote() {
            if (!this.selectedRequest || !this.selectedRequest.items) return 0;
            return this.selectedRequest.items.reduce((sum, item) => {
                const unitPrice = parseFloat(item.agent_quote) || 0;
                const quantity = parseInt(item.quantity, 10) || 0;
                return sum + (unitPrice * quantity);
            }, 0);
        },

        openDetail(data) {
            const itemsWithPrivateFields = data.items.map(item => ({
                ...item,
                agent_quote: ''
            }));

            this.selectedRequest = {
                ...data,
                items: itemsWithPrivateFields
            };

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
            this.availableTime = '';
            this.quoteRemarks = '';
        },

        submitQuote() {
            const total = this.selectedRequest.items.reduce((sum, item) => {
                const unitPrice = parseFloat(item.agent_quote) || 0;
                const quantity = parseInt(item.quantity, 10) || 0;
                return sum + (unitPrice * quantity);
            }, 0);

            if (total <= 0 || !this.availableTime.trim()) {
                alert('請針對商品填寫報價與可代購時段');
                return;
            }

            const payload = {
                request_list_id: this.selectedRequest.id,
                agent_quote_total: total,
                time: this.availableTime.trim(),
                comment: this.quoteRemarks.trim() || null,
                items: this.selectedRequest.items.map(item => ({
                    id: item.id,
                    agent_quote: item.agent_quote
                }))
            };

            fetch('/quotes', {
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
                alert(error.message);
            });
        }
    }" @keydown.escape.window="closeAll()" class="py-12 bg-gray-50 min-h-screen">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- 篩選區 -->
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
                <h3 class="text-lg font-bold text-gray-800">最新請託需求</h3>
                <span class="text-sm text-gray-400">找到 {{ $requestLists->total() }} 個符合條件的請託</span>
            </div>

            <!-- 請託單列表 -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($requestLists as $requestList)
                    @php
                        $countryCode = $requestList->country;
                        $countryTag = $countryLabels[$countryCode] ?? $countryCode;
                        $firstItem = $requestList->items->first();
                        $title = $requestList->title ?: ($firstItem->name ?? '未命名請託');

                        $isOwner = auth()->check() && (int) $requestList->user_id === (int) auth()->id();
                        $isFavorited = in_array((int) $requestList->id, $favoritedRequestListIds ?? [], true);
                        
                        $orderData = [
                            'id' => $requestList->id,
                            'title' => $title,
                            'store_name' => $requestList->store_name ?: '未提供',
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

                            <div class="flex gap-2">
                                <button type="button" 
                                    class="request-list-report-btn w-9 h-9 rounded-full transition flex items-center justify-center bg-gray-100 hover:bg-red-50 {{ $isOwner ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    data-request-list-id="{{ $requestList->id }}"
                                    title="{{ $isOwner ? '無法檢舉自己的請託單' : '檢舉請託單' }}"
                                    @if($isOwner) disabled aria-disabled="true" @endif>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ef4444" class="w-5 h-5" style="filter: drop-shadow(0 0 1px rgba(0,0,0,0.1));">
                                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                                    </svg>
                                </button>
                                <button type="button" 
                                    class="favorite-toggle w-9 h-9 rounded-full transition flex items-center justify-center {{ $isFavorited ? 'bg-pink-50 text-pink-500' : 'bg-gray-100 text-gray-400 hover:bg-pink-50 hover:text-pink-400' }}"
                                    data-request-list-id="{{ $requestList->id }}"
                                    @if($isOwner) disabled title="不能收藏自己的請託單" style="opacity: 0.5; cursor: not-allowed;" @endif>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12.001 4.529c2.349-2.532 6.15-2.533 8.498-.001 2.41 2.6 2.41 6.815 0 9.416l-7.66 8.266a1.14 1.14 0 0 1-1.677 0l-7.66-8.266c-2.41-2.601-2.41-6.817 0-9.416 2.348-2.532 6.149-2.531 8.499.001Z"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="mb-5 flex-1">
                            <p class="text-xs font-bold text-gray-400 mb-2 uppercase tracking-wider">請託內容</p>
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
                                        <span class="font-bold text-gray-400">店家詳細地址：</span>{{ $requestList->detail_address }}
                                    </p>
                                </div>
                            @endif 
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 min-w-0">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($requestList->user->name ?? 'User') }}&background=EEF2FF&color=4F46E5" class="w-7 h-7 rounded-full border border-gray-100" alt="avatar">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-[10px] text-gray-400 leading-none">請託人</span>
                                        <span class="text-xs text-gray-700 font-medium truncate">{{ $requestList->user->name ?? '未知' }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center">
                                    @php
                                        $myQuote = $requestList->offers->where('user_id', auth()->id())->first();
                                    @endphp

                                    @if($isOwner)
                                        @if($requestList->offers->count() > 0)
                                            <button type="button" @click="openQuoteList(@js($requestList->id))"
                                                class="px-5 py-2 bg-orange-500 text-white rounded-xl font-bold text-xs hover:bg-orange-600 transition shadow-md flex items-center gap-1">
                                                <i class="bi bi-people-fill"></i> 查看報價 ({{ $requestList->offers->count() }})
                                            </button>
                                        @else
                                            <span class="text-[10px] px-2 py-1 bg-red-50 text-red-400 rounded-lg font-bold">無法接取本人的清單</span>
                                        @endif

                                    @elseif($myQuote)
                                        <button type="button" disabled
                                            class="px-5 py-2 bg-green-600 text-white rounded-xl font-bold text-xs opacity-80 cursor-default flex items-center gap-1">
                                            <i class="bi bi-check-circle"></i> 已報價
                                        </button>

                                    @elseif($requestList->people)
                                        @if((int)$requestList->people === (int)auth()->id())
                                            <a href="{{ route('request-list.chat.show', $requestList) }}"  
                                            class="px-4 py-2 bg-green-500 text-white rounded-xl text-xs font-bold hover:bg-green-600 transition shadow-sm">
                                            聊一聊
                                            </a>
                                        @else
                                            <span class="text-[10px] px-2 py-1 bg-gray-100 text-gray-400 rounded-lg font-bold">已被承接</span>
                                        @endif

                                    @else
                                        <button type="button" @click="openDetail(@js($orderData))"
                                            class="px-5 py-2 bg-indigo-600 text-white rounded-xl font-bold text-xs hover:bg-indigo-700 transition shadow-md shadow-indigo-100 active:scale-95 flex items-center gap-1">
                                            <i class="bi bi-cart-plus"></i> 我要報價
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 檢舉貼文 Modal -->
                    <div id="request-report-modal-{{ $requestList->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60">
                        <div class="absolute inset-0" onclick="closeRequestReportModal({{ $requestList->id }})"></div>
                        <div class="relative w-full max-w-xl rounded-[2rem] bg-white shadow-2xl overflow-hidden">
                            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200">
                                <div>
                                    <p class="text-sm text-slate-500">檢舉請託單</p>
                                    <h3 class="text-xl font-bold text-slate-900">選擇檢舉類型並填寫原因</h3>
                                </div>
                                <button type="button" class="text-slate-400 hover:text-slate-700 text-2xl" onclick="closeRequestReportModal({{ $requestList->id }})">&times;</button>
                            </div>
                            <form class="report-form p-6" data-target-type="request_list" data-target-id="{{ $requestList->id }}" onsubmit="handleRequestReportSubmit(event, {{ $requestList->id }})">
                                @csrf
                                <input type="hidden" name="request_list_id" value="{{ $requestList->id }}">

                                <div class="mb-5">
                                    <label for="reportType-{{ $requestList->id }}" class="block text-sm font-semibold text-slate-700 mb-2">檢舉違規類型</label>
                                    <select id="reportType-{{ $requestList->id }}" name="report_type" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                        <option value="" selected disabled>請選擇違規類型</option>
                                        <option value="false_info">虛假信息</option>
                                        <option value="fraud">詐騙嫌疑</option>
                                        <option value="prohibited_items">違禁品</option>
                                        <option value="copyright">侵權</option>
                                        <option value="harassment">騷擾或威脅</option>
                                        <option value="spam">垃圾訊息</option>
                                        <option value="other">其他</option>
                                    </select>
                                </div>

                                <div class="mb-6">
                                    <label for="reportReason-{{ $requestList->id }}" class="block text-sm font-semibold text-slate-700 mb-2">檢舉原因</label>
                                    <textarea id="reportReason-{{ $requestList->id }}" name="reason" rows="5" class="w-full rounded-[1.5rem] border border-slate-200 p-4 text-sm text-slate-700 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="請詳細描述檢舉原因，最多 500 字"></textarea>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <button type="button" class="flex-1 py-3 rounded-2xl border border-slate-300 text-slate-600 hover:bg-slate-100 transition" onclick="closeRequestReportModal({{ $requestList->id }})">取消</button>
                                    <button type="submit" class="flex-1 py-3 rounded-2xl bg-rose-500 text-white font-semibold hover:bg-rose-600 transition">提交檢舉</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-20 bg-white rounded-[2rem] border-2 border-dashed border-gray-100">
                        <div class="text-gray-300 mb-2"><i class="bi bi-inbox text-5xl"></i></div>
                        <p class="text-gray-400 font-bold text-lg">目前沒有符合條件的請託需求</p>
                    </div>
                @endforelse
            </div>

            <!-- 【新增分頁導航區 + 強制覆寫亮色白色背景】：解決只顯示12筆而無法延續的問題 -->
            @if($requestLists->hasPages())
                <div class="agent-pagination mt-12 bg-white px-6 py-4 rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    {{ $requestLists->links() }}
                </div>
            @endif
        </div>

        <!-- [彈窗 1] 詳細資料 Modal -->
        <div x-show="showDetailModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
            <div class="absolute inset-0 bg-black/30" @click="closeAll()"></div>
            <div x-show="showDetailModal" x-transition class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-2xl text-left relative">
                <button @click="closeAll()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-indigo-700 mb-4" x-text="selectedRequest?.title"></h3>
                    <div class="space-y-1">
                        <div class="text-sm text-gray-500"><span class="font-bold">店家：</span><span x-text="selectedRequest?.store_name"></span></div>
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
                    <p class="text-gray-400 text-sm">請針對請託單內的每一項商品 provide 報價 (NTD)</p>
                </div>

                <!-- 商品報價列表 -->
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
                                    placeholder="輸入此商品之單價">
                            </div>
                        </div>
                    </template>

                    <!-- 預計代購日期 -->
                    <div class="pt-2">
                        <label class="block text-xs font-black text-gray-500 uppercase mb-3 tracking-widest flex items-center gap-2">
                            <i class="bi bi-calendar-check text-indigo-500"></i> 預計代購日期
                        </label>
                        <div class="relative">
                            <input type="date" x-model="availableTime" 
                                :min="new Date().toISOString().split('T')[0]"
                                :max="selectedRequest?.deadline"
                                class="w-full p-4 bg-white border-2 border-indigo-100 rounded-2xl text-sm font-bold text-gray-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 shadow-sm transition-all cursor-pointer">
                        </div>
                        <p class="text-[10px] text-amber-500 mt-2 font-bold" x-show="selectedRequest?.deadline">
                            <i class="bi bi-info-circle"></i> 
                            請購人要求之最後截止日為：<span x-text="selectedRequest.deadline"></span>
                        </p>
                    </div>

                    <!-- 報價備註 -->
                    <div class="pt-4 pb-8">
                        <label class="block text-xs font-black text-gray-500 uppercase mb-3 tracking-widest flex items-center gap-2">
                            <i class="bi bi-chat-left-text text-indigo-500"></i> 報價備註 (選填)
                        </label>
                        <textarea x-model="quoteRemarks" rows="3" 
                            class="w-full p-6 bg-white border-2 border-indigo-100 rounded-3xl text-sm font-bold text-gray-700 placeholder-gray-300 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 shadow-sm transition-all" 
                            placeholder="例如：此報價不含運費。"></textarea>
                    </div>
                </div>

                <!-- 報價 Modal 底部 -->
                <div class="p-8 pt-6 border-t bg-gray-50">
                    <div class="flex gap-4">
                        <button @click="closeAll()" 
                                class="flex-1 py-4 text-gray-500 font-black hover:text-gray-700">
                            取消
                        </button>
                        <button @click="submitQuote()"
                            :disabled="totalQuote <= 0 || !availableTime.trim()"
                            :class="(totalQuote <= 0 || !availableTime.trim()) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-indigo-700'"
                            class="flex-1 py-4 bg-indigo-600 text-white rounded-2xl font-black transition-all">
                            確認送出 (總報價:NT$<span x-text="totalQuote"></span>)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 將 Axios script 移出 style 區塊，解決瀏覽器編譯錯誤 -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

        /* ==========================================================================
           【分頁樣式白底美化 & 強制阻擋黑暗模式】
           ========================================================================== */
        .agent-pagination nav[role="navigation"] {
            background-color: #ffffff !important;
            color: #4b5563 !important; /* text-gray-600 */
        }

        /* 文字敘述與數字 (如 Showing 1 to 12 of 24 results) */
        .agent-pagination nav[role="navigation"] p {
            color: #4b5563 !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
        }

        /* 所有按鈕的通用樣式：基礎白底、深灰文字與柔和淺色邊框 */
        .agent-pagination nav[role="navigation"] a,
        .agent-pagination nav[role="navigation"] span {
            background-color: #ffffff !important;
            color: #4b5563 !important;
            border-color: #f1f5f9 !important; /* border-slate-100 */
            border-width: 1px !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            transition: all 0.2s ease-in-out !important;
        }

        /* 當前作用中的頁碼 (Active State) 改為高級靛藍底色+純白字體 */
        .agent-pagination nav[role="navigation"] span[aria-current="page"] > span {
            background-color: #4f46e5 !important; /* bg-indigo-600 */
            color: #ffffff !important;
            border-color: #4f46e5 !important;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.15) !important;
            font-weight: 700 !important;
        }

        /* 懸停 (Hover) 時的微交互反饋 */
        .agent-pagination nav[role="navigation"] a:hover {
            background-color: #f8fafc !important; /* bg-slate-50 */
            color: #4f46e5 !important; /* text-indigo-600 */
            border-color: #cbd5e1 !important; /* border-slate-300 */
            transform: translateY(-1px);
        }

        /* 左右箭頭圖示強制維持深色，不被黑暗模式淡化 */
        .agent-pagination nav[role="navigation"] svg {
            color: #4b5563 !important;
            display: inline-block !important;
        }
    </style>

    <script>
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

            document.querySelectorAll('.request-list-report-btn').forEach(button => {
                button.addEventListener('click', function () {
                    if (button.disabled) return;
                    const id = this.getAttribute('data-request-list-id');
                    if (!id) return;
                    const modal = document.getElementById(`request-report-modal-${id}`);
                    if (modal) {
                        modal.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                    }
                });
            });
        });

        function closeRequestReportModal(id) {
            const modal = document.getElementById(`request-report-modal-${id}`);
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function handleRequestReportSubmit(event, id) {
            event.preventDefault();
            const form = event.target;
            const reportType = form.querySelector('select[name="report_type"]').value;
            const reason = form.querySelector('textarea[name="reason"]').value.trim();

            if (!reportType) {
                alert('請選擇違規類型');
                return;
            }
            if (!reason) {
                alert('請輸入檢舉原因');
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            fetch("{{ route('reports.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    target_type: form.dataset.targetType,
                    target_id: form.dataset.targetId,
                    report_type: reportType,
                    reason: reason,
                }),
            })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || '檢舉送出失敗');
                }
                alert(data.message || '檢舉已送出，管理員會盡快審核。');
                closeRequestReportModal(id);
                form.reset();
            })
            .catch((error) => {
                alert(error.message || '檢舉送出失敗');
            })
            .finally(() => {
                submitBtn.disabled = false;
            });
        }
    </script>
</x-app-layout>