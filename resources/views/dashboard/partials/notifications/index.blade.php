<div x-data="{ 
    followSearch: '',
    showProfileModal: false,
    selectedAgent: null,
    removedIds: [], // 記錄在此次操作中取消追蹤的 ID

    // 輔助函式：確保回傳的是陣列，解決 JSON 字串顯示亂碼的問題
    getArray(data) {
        if (!data) return [];
        if (Array.isArray(data)) return data;
        try { return JSON.parse(data); } catch (e) { return []; }
    },

    // 開啟檔案彈窗
    openProfile(agent) {
        this.selectedAgent = agent;
        this.showProfileModal = true;
        document.body.style.overflow = 'hidden'; 
    },

    // 關閉彈窗
    closeProfile() {
        this.showProfileModal = false;
        this.selectedAgent = null;
        document.body.style.overflow = 'auto';
    },

    // 跳轉至首頁搜尋
    goToPostSearch(title, postId) {
        const searchUrl = '{{ route('home') }}?search=' + encodeURIComponent(title) + '&post_id=' + postId;
        window.location.href = searchUrl;
    },

    /**
     * 【核心修改】：取消追蹤邏輯
     * 1. 跳出詢問視窗
     * 2. 確定後發送請求
     * 3. 成功後讓卡片從畫面上消失
     */
    async unfollow(id) {
        if(!confirm('確定要取消追蹤這位代購人嗎？')) return;
        
        try {
            const response = await fetch('{{ route('follow.toggle') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ user_id: id })
            });
            const result = await response.json();
            
            // 只要回傳成功且狀態為「未追蹤」，就將 ID 加入移除清單
            if (result.status === 'success' && result.is_following === false) {
                this.removedIds.push(id);
            }
        } catch (error) {
            console.error('取消追蹤操作失敗', error);
        }
    }
}" @keydown.escape.window="closeProfile()">
    
    <!-- 頂部標題與搜尋區 -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-2xl font-black text-gray-800 flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-500 text-white rounded-xl flex items-center justify-center shadow-lg shadow-amber-200">
                    <i class="bi bi-person-check-fill"></i>
                </div>
                我的追蹤名單
            </h3>
            <p class="text-sm text-gray-500 mt-2 ml-1">您關注的代購職人，即時掌握連線動態。</p>
        </div>

        <div class="relative w-full md:w-80">
            <input type="text" x-model="followSearch" placeholder="搜尋追蹤對象名稱..." 
                class="w-full pl-11 pr-4 py-3 bg-white border-2 border-gray-100 rounded-2xl text-sm focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 shadow-sm transition-all">
            <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>

    <!-- 追蹤名單卡片區域 -->
    <div class="follow-list-container pr-2 -mr-2 overflow-y-auto custom-scrollbar" style="max-height: 1100px;">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 pb-4">
            @forelse($followings as $agent)
                <!-- 【關鍵點】：增加判斷，如果 ID 在 removedIds 裡則不顯示 -->
                <div x-show="(!removedIds.includes({{ $agent->id }})) && (followSearch === '' || '{{ $agent->nickname ?? $agent->name }}'.toLowerCase().includes(followSearch.toLowerCase()))"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-90"
                     class="group bg-white rounded-[2rem] border-2 border-gray-100 shadow-sm hover:shadow-xl hover:border-amber-200 transition-all duration-300 relative overflow-hidden flex flex-col">
                    
                    <div class="h-2 w-full bg-amber-400 opacity-20 group-hover:opacity-100 transition-opacity"></div>

                    <div class="p-8 flex flex-col items-center text-center flex-1">
                        <!-- 代購人頭像 -->
                        <div class="mb-5 relative">
                            <img src="{{ $agent->avatar_url }}" 
                                 class="w-24 h-24 rounded-full border-4 border-white shadow-md object-cover relative z-10">
                            <div class="absolute bottom-1 right-1 w-6 h-6 bg-green-500 border-4 border-white rounded-full z-20 shadow-sm"></div>
                        </div>

                        <h4 class="font-black text-gray-800 text-xl mb-1 group-hover:text-amber-600 transition-colors">
                            {{ $agent->nickname ?? $agent->name }}
                        </h4>
                        
                        <div class="inline-flex items-center gap-1.5 text-[11px] font-bold text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-100 mb-4">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>{{ $agent->agentApplication->country ?? '全球代購' }} 駐點</span>
                        </div>

                        <p class="text-sm text-gray-400 line-clamp-2 italic px-2 mb-6">
                            {{ $agent->bio ?? '這是一位低調的代購職人，尚未填寫自我介紹。' }}
                        </p>

                        <!-- 操作按鈕 -->
                        <div class="mt-auto w-full space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="openProfile({{ Js::from($agent) }})" 
                                   class="bg-gray-100 text-gray-600 py-3 rounded-2xl text-xs font-bold hover:bg-gray-100 transition border border-gray-100 flex items-center justify-center gap-1">
                                    <i class="bi bi-person-badge"></i> 檔案
                                </button>
                                <a href="{{ route('messages.index', ['partner' => $agent->id]) }}" 
                                   class="bg-amber-500 text-white py-3 rounded-2xl text-xs font-bold hover:bg-amber-600 shadow-lg shadow-amber-100 transition flex items-center justify-center gap-1">
                                    <i class="bi bi-chat-dots-fill"></i> 聊聊
                                </a>
                            </div>
                            
                            <button @click="unfollow({{ $agent->id }})" 
                                    class="w-full text-[11px] text-gray-300 hover:text-red-400 font-bold transition py-2 flex items-center justify-center gap-1">
                                <i class="bi bi-person-x-fill"></i> 取消追蹤
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-24 bg-white rounded-[3rem] border-4 border-dashed border-gray-50 text-center">
                    <div class="w-24 h-24 bg-amber-50 text-amber-300 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl shadow-inner">
                        <i class="bi bi-people"></i>
                    </div>
                    <h4 class="text-gray-800 font-black text-xl">目前還沒有追蹤任何人</h4>
                    <a href="{{ route('store') }}" class="mt-8 inline-flex items-center gap-2 bg-amber-500 text-white px-10 py-4 rounded-2xl font-black hover:bg-amber-600 transition shadow-xl shadow-amber-100">
                        <i class="bi bi-search text-lg"></i> 探索代購
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- 代購人個人檔案彈窗 (兩欄位佈局) -->
    <div x-show="showProfileModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" 
         x-cloak>
        <div x-show="showProfileModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeProfile()" 
             class="absolute inset-0 bg-emerald-950/60 backdrop-blur-sm"></div>
        
        <div x-show="showProfileModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-8"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-8"
             class="relative bg-white w-full max-w-5xl rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col border border-emerald-100">
            
            <button @click="closeProfile()" class="absolute top-6 right-6 z-20 w-10 h-10 bg-black/10 hover:bg-black/20 rounded-full flex items-center justify-center text-white transition backdrop-blur-md">
                <i class="bi bi-x-lg"></i>
            </button>

            <template x-if="selectedAgent">
                <div class="overflow-y-auto custom-scrollbar flex-1">
                    <!-- 頂部區塊 -->
                    <div class="bg-emerald-800 p-8 pt-12 pb-16 text-white relative">
                        <div class="flex flex-col md:flex-row items-center md:items-end gap-6">
                            <div class="relative">
                                <!-- 這裡路徑增加判斷防止 403 -->
                                <img :src="selectedAgent.avatar_url" 
                                     class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white shadow-xl object-cover bg-white">
                                <div class="absolute bottom-2 right-2 w-8 h-8 bg-green-500 border-4 border-white rounded-full"></div>
                            </div>
                            
                            <div class="text-center md:text-left flex-1 pb-2">
                                <h2 class="text-3xl md:text-5xl font-black mb-2" x-text="selectedAgent.nickname || selectedAgent.name"></h2>
                                <div class="flex flex-wrap justify-center md:justify-start gap-2">
                                    <span class="px-3 py-1 bg-white/20 rounded-full text-[10px] font-bold border border-white/30 uppercase tracking-widest">Certified Agent</span>
                                    <span class="px-3 py-1 bg-emerald-600 rounded-full text-[10px] font-bold border border-emerald-500" x-text="(selectedAgent.agent_application?.country || '台灣') + ' 駐點'"></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 md:pb-2">
                                <button disabled class="bg-white/20 border-white/40 text-white px-8 py-3 rounded-2xl font-black shadow-lg flex items-center gap-2 border-2 opacity-80 cursor-default">
                                    <i class="bi bi-person-check-fill"></i>
                                    <span>已追蹤</span>
                                </button>
                                <a :href="'/messages?partner=' + selectedAgent.id" class="bg-white text-emerald-800 px-8 py-3 rounded-2xl font-black shadow-lg hover:bg-emerald-50 transition flex items-center gap-2">
                                    <i class="bi bi-chat-heart-fill"></i> 立即聊一聊
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 內容區 -->
                    <div class="p-8 md:p-10">
                        <div class="grid grid-cols-1 lg:grid-cols-5 gap-10">
                            <!-- 左欄 -->
                            <div class="lg:col-span-2 space-y-10">
                                <div>
                                    <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">個人簡介</h5>
                                    <div class="bg-gray-50 p-6 rounded-[2rem] border border-gray-100">
                                        <p class="text-gray-600 leading-relaxed italic text-sm" x-text="selectedAgent.bio || '這是一位低調的代購職人！'"></p>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">可代購地區</h5>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="country in getArray(selectedAgent.purchasable_countries)">
                                            <span class="px-4 py-2 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-100" x-text="country"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- 右欄 -->
                            <div class="lg:col-span-3">
                                <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <i class="bi bi-megaphone text-emerald-500"></i> 目前活躍的代購團
                                </h5>
                                <div class="space-y-4">
                                    <template x-for="post in (selectedAgent.agent_posts || [])" :key="post.id">
                                        <div @click="goToPostSearch(post.title, post.id)"
                                             class="group flex gap-5 p-5 rounded-[2rem] border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50/30 transition-all duration-300 cursor-pointer">
                                            <div class="w-24 h-24 bg-emerald-50 text-emerald-500 rounded-2xl flex-shrink-0 flex items-center justify-center border border-emerald-100 shadow-sm group-hover:bg-emerald-100 transition">
                                                <i class="bi bi-bag-heart-fill text-4xl"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-start mb-1">
                                                    <h6 class="font-black text-gray-800 text-lg truncate group-hover:text-emerald-700 transition" x-text="post.title"></h6>
                                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100 px-3 py-1 rounded-full border border-emerald-200">OPEN</span>
                                                </div>
                                                <p class="text-xs text-gray-500 line-clamp-1 mb-4" x-text="post.description || '這是一則專業的代購團。'"></p>
                                                <div class="flex items-center gap-4">
                                                    <div class="flex items-center gap-1 text-[10px] font-bold text-gray-400"><i class="bi bi-geo-alt"></i><span x-text="post.country"></span></div>
                                                    <div class="flex items-center gap-1 text-[10px] font-bold text-gray-400"><i class="bi bi-calendar-check"></i><span x-text="(post.end_date ? post.end_date.split('T')[0] : '2026-05-10') + ' 截止'"></span></div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!selectedAgent.agent_posts || selectedAgent.agent_posts.length === 0">
                                        <div class="text-center py-16 bg-gray-50 rounded-[2rem] border-2 border-dashed border-gray-100 text-gray-400 text-sm">目前此代購人尚未發布任何貼文</div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<style>
    .follow-list-container { max-height: 1100px; }
    [x-cloak] { display: none !important; }

    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>