<x-app-layout>
    <!-- 使用 Alpine.js 管理彈窗狀態 -->
    <div x-data="{ 
        showModal: false, 
        activeAgent: null,
        openProfile(agent) {
            this.activeAgent = agent;
            this.showModal = true;
            document.body.style.overflow = 'hidden'; 
        },
        closeProfile() {
            this.showModal = false;
            this.activeAgent = null;
            document.body.style.overflow = 'auto'; 
        }
    }" @keydown.escape.window="closeProfile()">

        <!-- 頂部標題區 -->
        <div class="bg-emerald-800 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl font-black text-white mb-4">找代購</h1>
                <p class="text-emerald-100 opacity-80">
                    @if(request('country'))
                        正在瀏覽 <span class="text-yellow-400 underline">{{ request('country') }}</span> 的代購職人
                    @else
                        共有 {{ $agents->count() }} 位認證代購人在線為您服務
                    @endif
                </p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10">
            <!-- 搜尋與篩選列 -->
            <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-100 mb-12">
                <form action="{{ route('store') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-center">
                    <!-- 隱藏國家參數，確保搜尋時保留篩選狀態 -->
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

                    <!-- 國家篩選按鈕區 (替代原本的建立清單) -->
                    <div class="flex gap-2 w-full lg:w-auto">
                        <a href="{{ route('store', array_merge(request()->except('country'), ['country' => '日本'])) }}" 
                           class="flex-1 lg:flex-none px-6 py-4 rounded-2xl font-bold border-2 transition flex items-center justify-center gap-2 {{ request('country') == '日本' ? 'bg-emerald-600 border-emerald-600 text-white shadow-lg shadow-emerald-100' : 'border-gray-100 text-gray-500 hover:border-emerald-500 hover:text-emerald-600' }}">
                            🇯🇵 日本
                        </a>
                        <a href="{{ route('store', array_merge(request()->except('country'), ['country' => '韓國'])) }}" 
                           class="flex-1 lg:flex-none px-6 py-4 rounded-2xl font-bold border-2 transition flex items-center justify-center gap-2 {{ request('country') == '韓國' ? 'bg-emerald-600 border-emerald-600 text-white shadow-lg shadow-emerald-100' : 'border-gray-100 text-gray-500 hover:border-emerald-500 hover:text-emerald-600' }}">
                            🇰🇷 韓國
                        </a>
                        @if(request('country'))
                            <a href="{{ route('store', request()->except('country')) }}" class="p-4 rounded-2xl bg-red-50 text-red-500 hover:bg-red-100 transition flex items-center justify-center" title="清除篩選">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- 代購人卡片清單 -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-20">
                @forelse($agents as $agent)
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                        <div class="flex flex-col items-center text-center">
                            <!-- 代購人頭像 -->
                            <div class="relative mb-4 cursor-pointer" @click="openProfile({{ $agent->toJson() }})">
                                <img src="{{ $agent->avatar ? asset('storage/' . $agent->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($agent->name) . '&background=10b981&color=fff' }}" 
                                     class="w-24 h-24 rounded-full border-4 border-white shadow-md object-cover transition group-hover:scale-105">
                                <div class="absolute bottom-0 right-0 w-6 h-6 bg-green-500 border-2 border-white rounded-full"></div>
                            </div>

                            <h4 class="text-xl font-black text-gray-800 group-hover:text-emerald-600 transition">{{ $agent->nickname ?? $agent->name }}</h4>
                            <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest mt-1">PRO AGENT</span>

                            <!-- 可代購國家 -->
                            <div class="flex flex-wrap justify-center gap-2 mt-4 mb-6 min-h-[32px]">
                                @php
                                    $countries = json_decode($agent->purchasable_countries ?? '[]', true) ?: [];
                                @endphp
                                @forelse(array_slice($countries, 0, 3) as $country)
                                    <span class="px-3 py-1 bg-gray-50 text-gray-500 text-[10px] font-bold rounded-full border border-gray-100">
                                        {{ $country }}
                                    </span>
                                @empty
                                    <span class="text-[10px] text-gray-400 italic">全球代購中</span>
                                @endforelse
                                @if(count($countries) > 3)
                                    <span class="text-[10px] text-gray-400 font-bold">+{{ count($countries) - 3 }}</span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-3 w-full">
                                <button type="button" @click="openProfile({{ $agent->toJson() }})" 
                                    class="bg-gray-100 text-gray-600 py-3 rounded-2xl text-sm font-bold hover:bg-gray-200 transition">
                                    查看檔案
                                </button>
                                <button class="bg-emerald-500 text-white py-3 rounded-2xl text-sm font-bold hover:bg-emerald-600 shadow-lg shadow-emerald-100 transition flex items-center justify-center gap-2">
                                    <i class="bi bi-chat-fill"></i> 聊一聊
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center bg-white rounded-3xl border-2 border-dashed border-gray-100">
                        <div class="w-20 h-20 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                            <i class="bi bi-search"></i>
                        </div>
                        <p class="text-gray-400 font-medium">找不到符合條件的代購人，試試其他關鍵字或地區</p>
                        <a href="{{ route('store') }}" class="mt-4 text-emerald-600 font-bold hover:underline inline-block">顯示全部代購人</a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 代購人個人檔案彈跳視窗 (Modal 保持原樣) -->
        <div x-show="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" x-cloak>
            <div x-show="showModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="closeProfile()" class="absolute inset-0 bg-emerald-950/60 backdrop-blur-sm"></div>
            <div x-show="showModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-8" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-8" class="relative bg-white w-full max-w-4xl max-h-[90vh] rounded-[40px] shadow-2xl overflow-hidden flex flex-col">
                <button @click="closeProfile()" class="absolute top-6 right-6 z-10 w-10 h-10 bg-black/10 hover:bg-black/20 rounded-full flex items-center justify-center text-white transition backdrop-blur-md">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <div class="bg-emerald-800 p-8 pt-12 pb-16 relative text-white flex flex-col md:flex-row items-center md:items-end gap-6">
                        <img :src="activeAgent?.avatar ? '/storage/' + activeAgent.avatar : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(activeAgent?.name) + '&background=10b981&color=fff&size=128'" class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white shadow-xl object-cover bg-white">
                        <div class="text-center md:text-left flex-1 pb-2">
                            <h2 class="text-3xl md:text-4xl font-black mb-2" x-text="activeAgent?.nickname || activeAgent?.name"></h2>
                            <div class="flex flex-wrap justify-center md:justify-start gap-2">
                                <span class="px-3 py-1 bg-white/20 rounded-full text-[10px] font-bold border border-white/30 uppercase tracking-widest">Certified Agent</span>
                                <span class="px-3 py-1 bg-emerald-600 rounded-full text-[10px] font-bold border border-emerald-500" x-text="(activeAgent?.agent_application?.country || '台灣') + ' 駐點'"></span>
                            </div>
                        </div>
                        <button class="bg-white text-emerald-800 px-8 py-3 rounded-2xl font-black shadow-lg hover:bg-emerald-50 transition flex items-center gap-2 mb-2">
                            <i class="bi bi-chat-heart-fill"></i> 立即聊一聊
                        </button>
                    </div>
                    <div class="p-8 grid grid-cols-1 lg:grid-cols-3 gap-10">
                        <div class="space-y-8">
                            <div>
                                <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">個人簡介</h5>
                                <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100">
                                    <p class="text-gray-600 leading-relaxed italic text-sm" x-text="activeAgent?.bio || '這位代購人還沒寫自我介紹唷！'"></p>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">可代購地區</h5>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="country in JSON.parse(activeAgent?.purchasable_countries || '[]')">
                                        <span class="px-4 py-2 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-100" x-text="country"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-2">
                            <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <i class="bi bi-megaphone text-emerald-500"></i> 目前活躍的代購貼文
                            </h5>
                            <div class="space-y-4">
                                <template x-for="post in activeAgent?.agent_posts" :key="post.id">
                                    <div class="group flex gap-4 p-4 rounded-3xl border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50/30 transition-all duration-300">
                                        <div class="w-20 h-20 bg-gray-100 rounded-2xl flex-shrink-0 overflow-hidden relative">
                                            <img x-show="post.cover_image" :src="'/storage/' + post.cover_image" class="w-full h-full object-cover">
                                            <div x-show="!post.cover_image" class="w-full h-full flex items-center justify-center text-gray-300"><i class="bi bi-image"></i></div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex justify-between items-start">
                                                <h6 class="font-bold text-gray-800 truncate group-hover:text-emerald-600 transition" x-text="post.title"></h6>
                                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-md">OPEN</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1 line-clamp-2" x-text="post.description"></p>
                                            <div class="flex items-center gap-4 mt-3">
                                                <span class="text-[10px] text-gray-400 font-bold"><i class="bi bi-geo-alt me-1"></i><span x-text="post.country"></span></span>
                                                <span class="text-[10px] text-gray-400 font-bold"><i class="bi bi-calendar-check me-1"></i><span x-text="post.end_date.split('T')[0]"></span> 截止</span>
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
    </style>
</x-app-layout>