<div x-data="{ followSearch: '' }">
    <!-- 頂部標題與搜尋區 -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="bi bi-person-check text-amber-500"></i>
                我的追蹤名單
            </h3>
            <p class="text-sm text-gray-500 mt-1">您關注的代購職人，即時掌握他們的最新連線動態。</p>
        </div>

        <!-- 追蹤名單搜尋 -->
        <div class="relative w-full md:w-72">
            <input type="text" x-model="followSearch" placeholder="搜尋追蹤對象..." 
                class="w-full pl-10 pr-4 py-2 bg-white border-gray-200 rounded-xl text-sm focus:ring-amber-500 focus:border-amber-500 shadow-sm transition">
            <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>

    <!-- 追蹤名單卡片區域 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- 
            假設後端傳入的是 $followings (User 模型集合)
            這裡使用了 @forelse 來處理有資料與無資料的情況 
        --}}
        @forelse($followings as $agent)
            <div x-show="followSearch === '' || '{{ $agent->nickname ?? $agent->name }}'.includes(followSearch)"
                 class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
                
                <!-- 背景裝飾 -->
                <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50 rounded-bl-full -mr-10 -mt-10 opacity-50 group-hover:scale-110 transition-transform"></div>

                <div class="flex flex-col items-center text-center relative z-10">
                    <!-- 代購人頭像 -->
                    <div class="mb-4 relative">
                        <img src="{{ $agent->avatar ? asset('storage/' . $agent->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($agent->name) . '&background=f59e0b&color=fff' }}" 
                             class="w-20 h-20 rounded-full border-4 border-white shadow-sm object-cover">
                        <div class="absolute bottom-0 right-1 w-5 h-5 bg-green-500 border-2 border-white rounded-full" title="在線中"></div>
                    </div>

                    <!-- 資訊 -->
                    <h4 class="font-bold text-gray-800 text-lg">{{ $agent->nickname ?? $agent->name }}</h4>
                    
                    <div class="flex items-center gap-1 text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full mt-1">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>{{ $agent->agentApplication->country ?? '全球代購' }} 駐點</span>
                    </div>

                    <p class="text-xs text-gray-400 mt-4 line-clamp-1 italic">
                        {{ $agent->bio ?? '這是一位低調的代購職人...' }}
                    </p>

                    <!-- 操作按鈕 -->
                    <div class="grid grid-cols-1 w-full gap-2 mt-6">
                        <div class="flex gap-2">
                            <a href="{{ route('shop.show', $agent->id) }}" class="flex-1 bg-gray-50 text-gray-600 py-2.5 rounded-xl text-xs font-bold hover:bg-gray-100 transition">
                                查看檔案
                            </a>
                            <a href="{{ route('messages.index') }}?partner={{ $agent->id }}" class="flex-1 bg-amber-500 text-white py-2.5 rounded-xl text-xs font-bold hover:bg-amber-600 shadow-sm transition flex items-center justify-center gap-1">
                                <i class="bi bi-chat-dots-fill"></i> 聊一聊
                            </a>
                        </div>
                        
                        <!-- 取消追蹤按鈕 (透過 AJAX 調用之前建立的 FollowController) -->
                        <form action="{{ route('follow.toggle') }}" method="POST" class="w-full">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $agent->id }}">
                            <button type="submit" class="w-full text-[10px] text-gray-300 hover:text-red-400 font-medium transition py-1">
                                <i class="bi bi-person-x me-1"></i> 取消追蹤
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <!-- 空狀態 -->
            <div class="col-span-full py-20 bg-white rounded-[40px] border-2 border-dashed border-gray-100 text-center">
                <div class="w-20 h-20 bg-amber-50 text-amber-300 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="bi bi-people"></i>
                </div>
                <h4 class="text-gray-800 font-bold">目前沒有追蹤任何人</h4>
                <p class="text-gray-400 text-sm mt-2">快去探索優秀的代購職人，掌握第一手好物資訊！</p>
                <a href="{{ route('store') }}" class="mt-6 inline-flex items-center gap-2 bg-amber-500 text-white px-8 py-3 rounded-2xl font-bold hover:bg-amber-600 transition shadow-lg shadow-amber-100">
                    <i class="bi bi-search"></i> 探索代購人
                </a>
            </div>
        @endforelse
    </div>
</div>

<style>
    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>