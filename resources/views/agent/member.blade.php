<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-indigo-800 leading-tight">
                {{ __('代購人會員專區') }}
            </h2>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('agent.dashboard') }}" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-full text-sm font-medium text-indigo-600 hover:bg-indigo-50 transition shadow-sm">
                    <i class="bi bi-speedometer2"></i>
                    <span>返回接單大廳</span>
                </a>
            </div>
        </div>
    </x-slot>
                    <!-- Modal 彈窗 -->
                    <div id="favorite-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 hidden">
                        <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-xs text-center">
                            <div class="flex flex-col items-center mb-4">
                                <div class="w-14 h-14 rounded-full bg-pink-50 flex items-center justify-center mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-pink-500">
                                        <path d="M12.001 4.529c2.349-2.532 6.15-2.533 8.498-.001 2.41 2.6 2.41 6.815 0 9.416l-7.66 8.266a1.14 1.14 0 0 1-1.677 0l-7.66-8.266c-2.41-2.601-2.41-6.817 0-9.416 2.348-2.532 6.149-2.531 8.499.001Z"/>
                                    </svg>
                                </div>
                                <div class="font-bold text-pink-600 text-lg">取消收藏</div>
                            </div>
                            <div class="text-gray-700 mb-6">確定要取消收藏這個請購清單嗎？</div>
                            <div class="flex justify-center gap-4">
                                <button id="favorite-modal-confirm" class="px-4 py-2 bg-pink-500 text-white rounded-xl font-bold hover:bg-pink-600 transition">是</button>
                                <button id="favorite-modal-cancel" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition">否</button>
                            </div>
                        </div>
                    </div>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- 數據統計區 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-2xl">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">累計代購收入</p>
                        <h4 class="text-2xl font-bold text-gray-800">$128,450</h4>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-2xl">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">已完成訂單</p>
                        <h4 class="text-2xl font-bold text-gray-800">86 筆</h4>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 bg-yellow-50 text-yellow-500 rounded-xl flex items-center justify-center text-2xl">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">服務總評價</p>
                        <h4 class="text-2xl font-bold text-gray-800">4.9 / 5</h4>
                    </div>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- 左側：代購管理導航 -->
                <div class="w-full lg:w-1/4 space-y-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 bg-indigo-600 text-white font-bold flex items-center justify-between">
                            <span>管理工具箱</span>
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <nav class="p-2 space-y-1">
                            <!-- 我的代購連線 -->
                            <a href="#connections" class="flex items-center space-x-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                                <span>我的代購貼文</span>
                            </a>                         
                            <!-- 訂單管理 -->
                            <a href="#" class="flex items-center space-x-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                <span>訂單管理</span>
                            </a>
                            <!-- 代購商品管理 -->
                            <a href="#" class="flex items-center space-x-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                <span>代購商品管理</span>
                            </a>
                            <!-- 聊天訊息 -->
                            <a href="#" class="flex items-center space-x-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                <span>聊天訊息</span>
                            </a>  
                            <!-- 撥款紀錄 -->
                            <a href="#payments" class="flex items-center space-x-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                <span>撥款紀錄</span>
                            </a>
                            <!-- 收藏請購清單 -->
                            <a href="#favorites" class="flex items-center space-x-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                <span>收藏請購清單</span>
                            </a>
                            <!-- 評價中心 -->
                            <a href="#" class="flex items-center space-x-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                <span>評價中心</span>
                            </a>
                            <!-- 物流設定 -->
                            <a href="#" class="flex items-center space-x-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                                <span>物流設定</span>
                            </a>

                            <div class="border-t border-gray-50 my-2 pt-2"></div>
                            
                            <!-- 發布貼文 -->
                            <a href="#" class="flex items-center space-x-3 p-3 rounded-xl text-indigo-600 font-bold hover:bg-indigo-50 transition group">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="group-hover:underline">發布代購貼文</span>
                            </a>
                        </nav>
                    </div>
                    
                    <!-- 個人名片預覽  -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h5 class="font-bold text-gray-800 mb-4 text-sm">個人名片預覽</h5>
                    <div class="flex flex-col items-center p-4 bg-gray-50 rounded-2xl">
                        <a href="{{ route('agent.profile.edit') }}" class="relative group cursor-pointer mb-3">
                            <!-- 頭像主體 -->
                            <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=6366f1&color=fff' }}" 
                                 class="w-20 h-20 rounded-full border-4 border-white shadow-sm transition duration-300 group-hover:brightness-50 object-cover">
                            
                            <!-- 懸停顯示的淺淺 "設定" 文字與圖示 -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                                <i class="bi bi-gear-fill text-white text-xs mb-1"></i>
                                <span class="text-white text-[10px] font-bold tracking-widest">設定</span>
                            </div>
                        </a>
                        
                        <h6 class="font-bold text-gray-800 text-sm">{{ Auth::user()->nickname ?? Auth::user()->name }}</h6>
                        <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-widest font-bold text-indigo-500">認證代購職人</p>
                        
                        <!-- 新增：顯示可代購國家標籤 -->
                        @php
                            $countries = json_decode(Auth::user()->purchasable_countries ?? '[]', true);
                        @endphp
                        @if(!empty($countries))
                            <div class="flex flex-wrap justify-center gap-1 mt-3">
                                @foreach($countries as $country)
                                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-600 rounded text-[9px] font-bold">
                                        {{ $country == '日本' ? '🇯🇵' : '🇰🇷' }} {{ $country }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if(Auth::user()->bio)
                            <p class="text-[10px] text-gray-500 mt-3 text-center line-clamp-2 px-2 italic leading-relaxed">
                                "{{ Auth::user()->bio }}"
                            </p>
                        @endif

                    </div>
                </div>
            </div>

                <!-- 右側主管理區 -->
                <div class="w-full lg:w-3/4 space-y-8">
                    <!-- 我的代購連線 -->
                    <section id="connections" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

                        @php
                            $myAgentPosts = \App\Models\AgentPost::withCount('products')->where('user_id', Auth::id())
                                ->latest()
                                ->take(6)
                                ->get();
                        @endphp

                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-800">我的代購貼文</h3>
                            <a href="{{ route('agent.posts.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-indigo-700 transition">+發布貼文</a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($myAgentPosts as $post)
                                <div class="p-4 border border-gray-100 rounded-2xl flex gap-4 hover:border-indigo-200 transition">
                                    <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center text-gray-300 overflow-hidden">
                                        @if($post->cover_image)
                                            <img src="{{ asset('storage/'.$post->cover_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="bi bi-image text-xl"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h6 class="font-bold text-gray-800 text-sm truncate">【{{ $post->country }}】{{ $post->title }}</h6>
                                        <p class="text-[10px] text-gray-400">銷售期間: {{ optional($post->start_date)->format('Y-m-d') }} ~ {{ optional($post->end_date)->format('Y-m-d') }}</p>
                                        <div class="mt-2 flex gap-2">
                                            <span class="text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded">{{ $post->products_count }} 項商品</span>
                                            <span class="text-[10px] text-green-600 font-bold bg-green-50 px-2 py-0.5 rounded">{{ $post->status === 'open' ? '進行中' : $post->status }}</span>
                                        </div>
                                    </div>
                                </div>
                             @empty
                                <div class="col-span-2 p-8 border border-dashed border-gray-200 rounded-2xl text-center text-sm text-gray-400">
                                    尚未發布代購貼文,點擊右上角「+發布貼文」開始建立。
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <!-- 我的收藏請購清單 -->
                    <section id="favorites" class="bg-white rounded-2xl shadow-sm border border-pink-100 p-6">
                        <h3 class="text-lg font-bold text-pink-600 mb-6">我的收藏請購清單</h3>
                        <div class="space-y-4" id="favorite-list-block">
                            @php
                                $favoriteRequestLists = Auth::user()->favorites
                                    ->where('favoriteable_type', 'App\\Models\\RequestList')
                                    ->load('favoriteable')
                                    ->pluck('favoriteable')
                                    ->filter();
                            @endphp
                            @forelse($favoriteRequestLists as $favList)
                                <div class="favorite-list-item flex items-center gap-4 p-4 bg-pink-50 rounded-xl border border-pink-100" data-request-list-id="{{ $favList->id }}">
                                    <button type="button"
                                        class="favorite-remove-btn w-10 h-10 rounded-full bg-white text-pink-500 flex items-center justify-center shadow-sm border border-pink-100 transition hover:bg-pink-100"
                                        data-request-list-id="{{ $favList->id }}"
                                        aria-label="取消收藏"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                                            <path d="M12.001 4.529c2.349-2.532 6.15-2.533 8.498-.001 2.41 2.6 2.41 6.815 0 9.416l-7.66 8.266a1.14 1.14 0 0 1-1.677 0l-7.66-8.266c-2.41-2.601-2.41-6.817 0-9.416 2.348-2.532 6.149-2.531 8.499.001Z"/>
                                        </svg>
                                    </button>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-gray-800 truncate">{{ $favList->title ?? '未命名請購' }}</div>
                                        <div class="text-xs text-gray-400">截止：{{ optional($favList->deadline)->format('Y-m-d') ?? '-' }}</div>
                                    </div>
                                    <a href="{{ route('agent.dashboard', ['q' => $favList->title]) }}" class="text-xs text-pink-600 font-bold hover:underline">前往接單大廳</a>
                                </div>
                            @empty
                                <div class="text-gray-400 text-sm text-center py-8">尚未收藏任何請購清單</div>
                            @endforelse
                        </div>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function () {
                                                let pendingRemoveId = null;
                                                const modal = document.getElementById('favorite-modal');
                                                const confirmBtn = document.getElementById('favorite-modal-confirm');
                                                const cancelBtn = document.getElementById('favorite-modal-cancel');
                                                // 開啟 modal
                                                document.querySelectorAll('.favorite-remove-btn').forEach(function(btn) {
                                                    btn.addEventListener('click', function() {
                                                        pendingRemoveId = btn.getAttribute('data-request-list-id');
                                                        modal.classList.remove('hidden');
                                                    });
                                                });
                                                // 關閉 modal
                                                cancelBtn.addEventListener('click', function() {
                                                    modal.classList.add('hidden');
                                                    pendingRemoveId = null;
                                                });
                                                // 確認取消收藏
                                                confirmBtn.addEventListener('click', function() {
                                                    if (!pendingRemoveId) return;
                                                    fetch("{{ route('favorite.toggle') }}", {
                                                        method: 'POST',
                                                        headers: {
                                                            'Content-Type': 'application/json',
                                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                                                            'Accept': 'application/json',
                                                        },
                                                        body: JSON.stringify({ request_list_id: pendingRemoveId })
                                                    })
                                                    .then(response => response.json())
                                                    .then(data => {
                                                        if (data.status === 'removed') {
                                                            // 從畫面移除該收藏
                                                            const item = document.querySelector('.favorite-list-item[data-request-list-id="' + pendingRemoveId + '"]');
                                                            if (item) item.remove();
                                                            // 通知接單大廳同步變灰
                                                            window.localStorage.setItem('favorite-removed', pendingRemoveId);
                                                        }
                                                        modal.classList.add('hidden');
                                                        pendingRemoveId = null;
                                                    });
                                                });
                                            });
                                        </script>
                    </section>

                    <!-- 撥款紀錄 -->
                    <section id="payments" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-6">最近撥款紀錄</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-transparent hover:border-indigo-100 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-green-500 shadow-sm border border-gray-100">
                                        <i class="bi bi-wallet2"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800">提領至 台灣銀行 (***882)</div>
                                        <div class="text-[10px] text-gray-400">2025-02-15 10:00</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-gray-800">-$15,000</div>
                                    <div class="text-[10px] text-green-600 font-bold">撥款成功</div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>