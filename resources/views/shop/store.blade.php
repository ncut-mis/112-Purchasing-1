<x-app-layout>
    @php
        // 【核心優化】：在最前端就過濾掉登入者本人的卡片，確保統計數量與實際顯示的卡片 100% 同步
        $visibleAgents = $agents->filter(function($agent) {
            return !(Auth::check() && $agent->id === Auth::id());
        });
        $displayCount = $visibleAgents->count();
    @endphp

    <!-- 使用 Alpine.js 管理彈窗狀態、追蹤狀態以及分頁加載限制 -->
    <div x-data="{ 
        showModal: false, 
        activeAgent: null,
        limit: 12, // 預設最多顯示 12 個代購人
        totalCount: {{ $displayCount }},
        // 初始化：確保 ID 都是數字型態以利比對
        followedAgents: {{ Js::from(Auth::user() ? Auth::user()->followings->pluck('id')->map(fn($id) => (int)$id) : []) }}, 
        
        // 初始化監聽器：實現使用者下拉時自動載入更多
        init() {
            window.addEventListener('scroll', () => {
                // 當使用者下拉滾動至接近底部 300px 時，自動載入下一頁 (加載 12 筆)
                if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 300)) {
                    if (this.limit < this.totalCount) {
                        this.limit += 12;
                    }
                }
            });
        },

        // 輔助函式：確保回傳的是陣列，解決 JSON 字串顯示為亂碼字元的問題
        getArray(data) {
            if (!data) return [];
            if (Array.isArray(data)) return data;
            try {
                // 如果是字串則嘗試解析 JSON
                return JSON.parse(data);
            } catch (e) {
                return [];
            }
        },

        // 跳轉至「大廳 (首頁)」並搜尋特定貼文
        goToPostSearch(title, postId) {
            // 導向首頁，並附帶 search 與 post_id 參數以確保精準搜尋
            const searchUrl = '{{ route('home') }}?search=' + encodeURIComponent(title) + '&post_id=' + postId;
            window.location.href = searchUrl;
        },

        openProfile(agent) {
            this.activeAgent = agent;
            this.showModal = true;
            document.body.style.overflow = 'hidden'; 
        },
        closeProfile() {
            this.showModal = false;
            // 延遲清空 activeAgent 避免動畫期間路徑變為 null 產生 403 錯誤
            setTimeout(() => { if(!this.showModal) this.activeAgent = null; }, 300);
            document.body.style.overflow = 'auto'; 
        },
        // 檢查是否已追蹤
        checkIsFollowed(id) {
            if (!id) return false;
            return this.followedAgents.includes(Number(id));
        },
        // 【核心邏輯：切換追蹤】
        async toggleFollow(id) {
            @guest
                window.location.href = {{ Js::from(route('login')) }};
                return;
            @endguest

            if (!id) return;

            try {
                const token = document.querySelector('meta[name=csrf-token]')?.content;
                if (!token) return;

                const response = await fetch({{ Js::from(route('follow.toggle')) }}, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ user_id: id }) // 這裡對應後端 controller 慣用參數
                });

                const result = await response.json();

                if (result.status === 'success') {
                    const agentId = Number(id);
                    if (result.is_following) {
                        if (!this.checkIsFollowed(agentId)) {
                            this.followedAgents = [...this.followedAgents, agentId];
                        }
                    } else {
                        this.followedAgents = this.followedAgents.filter(aid => aid !== agentId);
                    }
                }
            } catch (error) {
                console.error('追蹤操作失敗:', error);
            }
        }
    }" @keydown.escape.window="closeProfile()">

        <!-- 頂部標題區 -->
        <div class="bg-emerald-800 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl font-black text-white mb-4">找代購</h1>
                <p class="text-emerald-100 opacity-80">
                    @if(request('country'))
                        正在瀏覽 <span class="text-yellow-400 underline">{{ request('country') }}</span> 的代購職人 (共有 {{ $displayCount }} 位)
                    @else
                        共有 {{ $displayCount }} 位認證代購人在線為您服務
                    @endif
                </p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10">
            <!-- 搜尋與篩選列 -->
            <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-100 mb-12">
                <form action="{{ route('store') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-center">
                    <input type="hidden" name="country" value="{{ request('country') }}">

                    <div class="relative flex-1 w-full">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="輸入代購人名稱或關鍵字..." 
                            class="w-full pl-12 pr-4 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-emerald-500">
                        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    
                    <button type="submit" class="w-full lg:w-auto bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-2xl font-bold transition flex items-center justify-center gap-2">
                        <i class="bi bi-search"></i> 搜尋
                    </button>

                    <div class="h-8 w-px bg-gray-100 hidden lg:block mx-2"></div>

                    <div class="flex gap-3 items-end w-full lg:w-auto">
                        <div class="flex-1 lg:w-48">
                            <select name="country" 
                                onchange="this.form.submit()" 
                                class="form-select rounded-2xl border-2 shadow-sm {{ request('country') ? 'border-emerald-500 shadow-emerald-100' : 'border-gray-200' }}"
                                style="font-weight: 600; height: 56px;">
                                <option value="">🌍 所有國家</option>
                                <option value="日本" {{ request('country') == '日本' ? 'selected' : '' }}>JP 日本</option>
                                <option value="韓國" {{ request('country') == '韓國' ? 'selected' : '' }}>KR 韓國</option>
                                <option value="美國" {{ request('country') == '美國' ? 'selected' : '' }}>US 美國</option>
                                <option value="英國" {{ request('country') == '英國' ? 'selected' : '' }}>UK 英國</option>
                            </select>
                        </div>
    
                        @if(request('country'))
                        <a href="{{ route('store', request()->except('country')) }}" 
                        class="p-4 rounded-2xl bg-red-50 border-2 border-red-100 text-red-500 hover:bg-red-100 shadow-sm transition flex items-center justify-center h-14" 
                        title="清除國家篩選">
                            <i class="bi bi-x-lg fs-4"></i>
                        </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- 代購人卡片清單 -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-20">
                <!-- 核心優化：將原本的迴圈改為附帶 index 陣列，以便進行限制與動態載入 -->
                @forelse($visibleAgents->values() as $index => $agent)
                    <div x-show="{{ $index }} < limit"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0 translate-y-6"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex flex-col h-full">
                        
                        <div class="flex flex-col items-center text-center flex-1">
                            <div class="relative mb-4 cursor-pointer" @click="openProfile({{ Js::from($agent) }})">
                                <img src="{{ $agent->avatar_url }}" 
                                     class="w-24 h-24 rounded-full border-4 border-white shadow-md object-cover transition group-hover:scale-105">
                                <div class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 border-2 border-white rounded-full"></div>
                            </div>

                            <h4 class="text-xl font-black text-gray-800 group-hover:text-emerald-600 transition">{{ $agent->nickname ?? $agent->name }}</h4>
                            <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest mt-1">PRO AGENT</span>

                            <div class="flex flex-wrap justify-center gap-2 mt-4 mb-6 min-h-[32px]">
                                @php
                                    $countriesData = $agent->purchasable_countries;
                                    if (is_array($countriesData)) {
                                        $countries = $countriesData;
                                    } else {
                                        $countries = json_decode($countriesData ?? '[]', true) ?? [];
                                        if (is_string($countries)) { $countries = json_decode($countries, true) ?? []; }
                                    }
                                    if (!is_array($countries)) { $countries = []; }
                                @endphp

                                @forelse(array_slice($countries, 0, 3) as $country)
                                    <span class="px-3 py-1 bg-gray-50 text-gray-500 text-[10px] font-bold rounded-full border border-gray-100">{{ $country }}</span>
                                @empty
                                    <span class="text-[10px] text-gray-400 italic">全球代購中</span>
                                @endforelse

                                @if(count($countries) > 3)
                                    <span class="text-[10px] text-gray-400 font-bold">+{{ count($countries) - 3 }}</span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-3 w-full mt-auto">
                                <button type="button" @click="openProfile({{ Js::from($agent) }})" 
                                    class="bg-gray-100 text-gray-600 py-3 rounded-2xl text-sm font-bold hover:bg-gray-200 transition">
                                    查看檔案
                                </button>
                                <button
                                    class="bg-emerald-500 text-white py-3 rounded-2xl text-sm font-bold hover:bg-emerald-600 shadow-lg shadow-emerald-100 transition flex items-center justify-center gap-2"
                                    @click="
                                        @auth
                                            window.location.href = '{{ route('messages.index') }}?partner=' + {{ Js::from($agent->id) }};
                                        @else
                                            window.location.href = {{ Js::from(route('login')) }};
                                        @endauth
                                    ">
                                    <i class="bi bi-chat-fill"></i> 聊一聊
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center bg-white rounded-3xl border-2 border-dashed border-gray-100">
                        <p class="text-gray-400 font-medium">目前暫時沒有認證代購人在線</p>
                    </div>
                @endforelse

                <!-- 【新增載入更多按鈕區塊】：手動載入與下拉載入雙重支援 -->
                <template x-if="limit < totalCount">
                    <div class="col-span-full text-center mt-12">
                        <button @click="limit += 12" class="bg-emerald-500 hover:bg-emerald-600 text-white px-10 py-4 rounded-2xl font-bold shadow-lg shadow-emerald-100 transition duration-300 transform hover:-translate-y-0.5 inline-flex items-center gap-2">
                            <span>載入更多代購人</span>
                            <i class="bi bi-chevron-down animate-bounce"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- 代購人個人檔案彈跳視窗 -->
        <div x-show="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" x-cloak>
            <div x-show="showModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeProfile()" class="absolute inset-0 bg-emerald-950/60 backdrop-blur-sm"></div>
            
            <div x-show="showModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-8" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-8" class="relative bg-white w-full max-w-4xl max-h-[90vh] rounded-[40px] shadow-2xl overflow-hidden flex flex-col">
                <button @click="closeProfile()" class="absolute top-6 right-6 z-20 w-10 h-10 bg-black/10 hover:bg-black/20 rounded-full flex items-center justify-center text-white transition backdrop-blur-md">
                    <i class="bi bi-x-lg"></i>
                </button>

                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <div class="bg-emerald-800 p-8 pt-12 pb-16 relative text-white flex flex-col md:flex-row items-center md:items-end gap-6">
                        <template x-if="activeAgent">
                            <img :src="activeAgent.avatar_url" 
                                 class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white shadow-xl object-cover bg-white">
                        </template>
                        
                        <div class="text-center md:text-left flex-1 pb-2">
                            <h2 class="text-3xl md:text-4xl font-black mb-2" x-text="activeAgent?.nickname || activeAgent?.name"></h2>
                            <div class="flex flex-wrap justify-center md:justify-start gap-2">
                                <span class="px-3 py-1 bg-white/20 rounded-full text-[10px] font-bold border border-white/30 uppercase tracking-widest">Certified Agent</span>
                                <span class="px-3 py-1 bg-emerald-600 rounded-full text-[10px] font-bold border border-emerald-500" x-text="(activeAgent?.agent_application?.country || '台灣') + ' 駐點'"></span>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3 mb-2">
                            <button 
                                @click="toggleFollow(activeAgent?.id)"
                                :class="checkIsFollowed(activeAgent?.id) ? 'bg-white/20 border-white/40 text-white' : 'bg-white text-emerald-800 border-white'"
                                class="px-8 py-3 rounded-2xl font-black shadow-lg transition flex items-center gap-2 border-2 hover:scale-105"
                            >
                                <i class="bi" :class="checkIsFollowed(activeAgent?.id) ? 'bi-person-check-fill' : 'bi-person-plus-fill'"></i>
                                <span x-text="checkIsFollowed(activeAgent?.id) ? '已追蹤' : '追蹤'"></span>
                            </button>

                            <button
                                class="bg-white text-emerald-800 px-8 py-3 rounded-2xl font-black shadow-lg hover:bg-emerald-50 transition flex items-center gap-2"
                                @click="
                                    @auth
                                        window.location.href = '{{ route('messages.index') }}?partner=' + activeAgent?.id;
                                    @else
                                        window.location.href = {{ Js::from(route('login')) }};
                                    @endauth
                                ">
                                <i class="bi bi-chat-heart-fill"></i> 立即聊一聊
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-8 grid grid-cols-1 lg:grid-cols-3 gap-10">
                        <div class="space-y-8">
                            <div>
                                <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">個人簡介</h5>
                                <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                                    <p class="text-gray-600 leading-relaxed italic text-sm" x-text="activeAgent?.bio || '這是一位低調的代購職人！'"></p>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">可代購地區</h5>
                                <div class="flex flex-wrap gap-2">
                                    <!-- 使用 getArray 修復地區顯示為 JSON 字元的亂碼問題 -->
                                    <template x-for="country in getArray(activeAgent?.purchasable_countries)">
                                        <span class="px-4 py-2 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-100" x-text="country"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-2">
                            <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <i class="bi bi-megaphone text-emerald-500"></i> 目前活躍的代購團
                            </h5>
                            <div class="space-y-4">
                                <template x-for="post in (activeAgent?.agent_posts || [])" :key="post.id">
                                    <!-- 加入點擊事件：點擊貼文卡片跳轉至首頁搜尋 -->
                                    <div @click="goToPostSearch(post.title, post.id)" 
                                         class="group flex gap-4 p-4 rounded-3xl border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50/30 transition-all duration-300 cursor-pointer">
                                        <div class="w-20 h-20 bg-gray-100 rounded-2xl flex-shrink-0 overflow-hidden relative">
                                            <template x-if="post.cover_image_url">
                                                <img :src="post.cover_image_url" class="w-full h-full object-cover">
                                            </template>
                                            <div x-show="!post.cover_image_url" class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-100">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex justify-between items-start">
                                                <h6 class="font-bold text-gray-800 truncate group-hover:text-emerald-600 transition" x-text="post.title"></h6>
                                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-md">OPEN</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1 line-clamp-2" x-text="post.description"></p>
                                            <div class="flex items-center gap-4 mt-3">
                                                <span class="text-[10px] text-gray-400 font-bold"><i class="bi bi-geo-alt me-1"></i><span x-text="post.country"></span></span>
                                                <span class="text-[10px] text-gray-400 font-bold"><i class="bi bi-calendar-check me-1"></i><span x-text="post.end_date?.split('T')[0]"></span> 截止</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!activeAgent?.agent_posts || activeAgent.agent_posts.length === 0">
                                    <div class="text-center py-12 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-100"><p class="text-gray-400 text-sm">目前此代購人尚未發布任何貼文</p></div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</x-app-layout>