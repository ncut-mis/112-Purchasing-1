<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-indigo-800 leading-tight">
                {{ __('代購人專區') }}
            </h2>
            
            <div class="flex items-center gap-3">
                <a href="{{ url('/agent/member') }}" class="text-gray-400 hover:text-gray-600 transition flex items-center">
                <i class="bi bi-chevron-left text-xl"></i>
            </a>
            </div>
        </div>
    </x-slot>

    <!-- 使用 Alpine.js 控制分頁，預設顯示 'posts' -->
    <div x-data="{ activeTab: {{ json_encode(request()->query('tab', 'posts')) }} }" class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- 數據統計區 (始終顯示) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-2xl">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">累計代購收入</p>
                        <h4 class="text-2xl font-bold text-gray-800">${{ number_format((float)$totalIncome, 0) }}</h4>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-2xl">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">已完成訂單</p>
                        <h4 class="text-2xl font-bold text-gray-800">{{ $finishedOrdersCount }} 筆</h4>
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
                <!-- 左側：管理工具箱 (完整還原版) -->
                <div class="w-full lg:w-1/4 space-y-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 bg-indigo-600 text-white font-bold flex items-center justify-between">
                            <span>管理工具箱</span>
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <nav class="p-2 space-y-1">
                            <!-- 1. 我的代購貼文 -->
                            <a href="#" @click.prevent="activeTab = 'posts'" :class="activeTab === 'posts' ? 'bg-indigo-50 text-indigo-600 font-bold' : 'text-gray-600'" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition">
                                <i class="bi bi-megaphone-fill text-lg"></i>
                                <span>我的代購團</span>
                            </a>
                            <!-- 2. 請託單管理 -->
                            <a href="#" @click.prevent="activeTab = 'order-management'" :class="activeTab === 'order-management' ? 'bg-indigo-50 text-indigo-600 font-bold' : 'text-gray-600'" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition">
                                <i class="bi bi-file-earmark-medical text-indigo-500 text-lg"></i>
                                <span>請託單管理</span>
                            </a>
                            <!-- 3. 代購貼文管理 -->
                            <a href="#" @click.prevent="activeTab = 'product-management'" :class="activeTab === 'product-management' ? 'bg-blue-50 text-blue-600 font-bold' : 'text-gray-600'" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition">
                                <i class="bi bi-box text-blue-500 text-lg"></i>
                                <span>代購團管理</span>
                            </a>
                            <!-- 4. 聊天訊息 -->
                            <a href="{{ route('agent.chat') }}" 
                                class="flex items-center gap-3 p-3 rounded-xl transition {{ request()->routeIs('agent.chat') ? 'bg-blue-50 text-blue-600 font-bold' : 'text-gray-600 hover:bg-gray-50' }}">
                                <i class="bi bi-chat-dots text-lg {{ request()->routeIs('agent.chat') ? 'text-blue-600' : 'text-blue-400' }}"></i>
                                <span>聊天訊息</span>
                            </a>
                            <!-- 5. 撥款紀錄 -->
                             <a href="#" @click.prevent="activeTab = 'payouts'" :class="activeTab === 'payouts' ? 'bg-emerald-50 text-emerald-600 font-bold' : 'text-gray-600'" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition">
                                <i class="bi bi-wallet2 text-emerald-500 text-lg"></i>
                                <span>撥款紀錄</span>
                            </a>
                            <!-- 6. 收藏請託單 -->
                            <a href="#" @click.prevent="activeTab = 'favorites'" :class="activeTab === 'favorites' ? 'bg-pink-50 text-pink-600 font-bold' : 'text-gray-600'" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition">
                                <i class="bi bi-heart text-pink-500 text-lg"></i>
                                <span>收藏請託單</span>
                            </a>
                            <!-- 7. 歷史紀錄 -->
                            <a href="{{ route('agent.member', ['tab' => 'agent-history']) }}"
                               @click.prevent="activeTab = 'agent-history'"
                               :class="activeTab === 'agent-history' ? 'bg-amber-50 text-amber-600 font-bold' : 'text-gray-600'"
                               class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition">
                                <i class="bi bi-clock-history text-lg text-amber-400"></i>
                                <span>歷史紀錄</span>
                            </a>
                            <!-- 8. 物流設定 -->
                           <a href="{{ route('logistics.index') }}" 
                            class="flex items-center gap-3 p-3 rounded-xl transition {{ request()->routeIs('logistics.*') ? 'bg-indigo-50 text-indigo-600 shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                                
                                <i class="bi bi-truck text-lg {{ request()->routeIs('logistics.*') ? 'text-indigo-600' : 'text-indigo-500' }}"></i>
                                
                                <span class="font-bold">物流設定</span>
                            </a>

                            <div class="border-t border-gray-50 my-2 pt-2"></div>
                            

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
                            // 取得原始資料
                            $countriesData = Auth::user()->purchasable_countries;
                            
                            // 核心修正邏輯：判斷是陣列還是 JSON 字串
                            if (is_array($countriesData)) {
                                $countries = $countriesData;
                            } else {
                                // 如果是字串，嘗試解析；若解析失敗則給予空陣列
                                $countries = json_decode($countriesData ?? '[]', true) ?? [];
                                
                                // 二次防呆：如果解析出來依然是字串 (Double Encoding 情況)，再解析一次
                                if (is_string($countries)) {
                                    $countries = json_decode($countries, true) ?? [];
                                }
                            }
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

                <!-- 右側主內容區 -->
                <div class="w-full lg:w-3/4 space-y-8">

                    
                    <!-- 分頁一：我的代購貼文 (預設顯示) -->
                    <div x-show="activeTab === 'posts'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4">
                        

                      <!-- 我的代購連線 -->
                      <section id="connections" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        @php
                            $myAgentPosts = \App\Models\AgentPost::with(['products'])->withCount('products')
                                ->where('user_id', Auth::id())
                                ->whereNotIn('status', ['completed'])
                                ->latest()
                                ->take(6)
                                ->get();
                        @endphp

                        <div class="flex justify-between items-center mb-6">
                             <div>
                                <h3 class="text-lg font-bold text-gray-800">我的代購貼文</h3>
                            </div>
                            <a href="{{ route('agent.posts.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-indigo-700 transition">+發布貼文</a>
                        </div>

                        @if (session('status'))
                            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($myAgentPosts as $post)
                                <div class="p-4 border border-gray-100 rounded-2xl flex gap-4 hover:border-indigo-200 transition">
                                    <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center text-gray-300 overflow-hidden">
                                        @if($post->cover_image_url)
                                            <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="bi bi-image text-xl"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h6 class="font-bold text-gray-800 text-sm truncate">【{{ $post->country }}】{{ $post->title }}</h6>
                                        <p class="text-[10px] text-gray-400">銷售期間: {{ optional($post->start_date)->format('Y-m-d') }} ~ {{ optional($post->end_date)->format('Y-m-d') }}</p>
                                        <div class="mt-2 flex gap-2 flex-wrap">
                                            <span class="text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded">{{ $post->products_count }} 項商品</span>
                                             @php
                                                $statusLabel = match($post->status) {
                                                    'draft'     => '編輯中',
                                                    'open'      => '進行中',
                                                    'closed'    => '已截單',
                                                    'shipped'   => '已出貨',
                                                    'arrivaled' => '已到貨',
                                                    'completed' => '已完成',
                                                    default     => $post->status,
                                                };
                                                $statusClasses = match($post->status) {
                                                    'draft'     => 'text-amber-600 bg-amber-50',
                                                    'open'      => 'text-green-600 bg-green-50',
                                                    'shipped'   => 'text-blue-600 bg-blue-50',
                                                    'arrivaled' => 'text-indigo-600 bg-indigo-50',
                                                    'completed' => 'text-gray-500 bg-gray-100',
                                                    default     => 'text-green-600 bg-green-50',
                                                };
                                            @endphp
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $statusClasses }}">{{ $statusLabel }}</span>
                                        </div>
                                        <div class="mt-3 flex gap-2 flex-wrap">
                                            <button type="button" class="agent-post-view-btn text-[11px] px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition" data-modal-id="agent-post-view-modal-{{ $post->id }}">檢視</button>
                                            @if($post->status === 'draft')
                                                <button type="button" class="agent-post-edit-btn text-[11px] px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition" data-modal-id="agent-post-edit-modal-{{ $post->id }}">編輯</button>
                                                <form method="POST" action="{{ route('agent.posts.destroy', $post) }}" onsubmit="return confirm('確定要刪除這篇編輯中的代購貼文嗎？');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-[11px] px-3 py-1.5 rounded-lg bg-rose-50 text-rose-600 font-semibold hover:bg-rose-100 transition">刪除</button>
                                                </form>
                                                <form method="POST" action="{{ route('agent.posts.submit', $post) }}" onsubmit="return confirm('送出後會顯示在首頁最新代購連線，確定送出？');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-[11px] px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition">送出</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-2 p-8 border border-dashed border-gray-200 rounded-2xl text-center text-sm text-gray-400">
                                    尚未發布代購貼文，點擊右上角「+ 發布貼文」開始建立。
                                </div>
                            @endforelse
                        </div>

                        @foreach($myAgentPosts as $post)
                            <div id="agent-post-view-modal-{{ $post->id }}" class="agent-post-modal fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
                                <div class="bg-white w-full max-w-5xl rounded-2xl shadow-xl p-6 max-h-[88vh] overflow-y-auto relative">
                                    <button type="button" class="modal-close-btn absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                                    <h4 class="text-xl font-bold text-gray-800 mb-2">{{ $post->title }}</h4>
                                    <p class="text-sm text-gray-500 mb-1">國家：{{ $post->country }}</p>
                                     <p class="text-sm text-gray-500 mb-1">狀態：{{ $post->status === 'draft' ? '編輯中' : ($post->status === 'open' ? '進行中' : $post->status) }}</p>
                                    <p class="text-sm text-gray-500 mb-1">銷售期間：{{ optional($post->start_date)->format('Y-m-d') }} ~ {{ optional($post->end_date)->format('Y-m-d') }}</p>
                                    <p class="text-sm text-gray-500 mb-1 whitespace-pre-line">描述訊息：{{ $post->description }}</p>
                                    <div class="mt-6 border-t pt-4">
                                        <h5 class="font-bold text-gray-800 mb-3">商品規格</h5>
                                        <div class="space-y-3">
                                            @foreach($post->products as $product)
                                                <div class="rounded-xl border border-gray-100 p-3 flex items-center gap-4">
                                                    <div class="w-20 h-20 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center text-gray-300">
                                                        @if($product->display_image_url)
                                                            <img src="{{ $product->display_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                                        @else
                                                            <i class="bi bi-image text-2xl"></i>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-base font-semibold text-gray-800 truncate">{{ $product->name }}</p>
                                                        <p class="text-sm text-gray-500">單價：NT$ {{ number_format((float) $product->price, 0) }}</p>
                                                        <p class="text-sm text-gray-500">最高數量：{{ $product->max_quantity }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                              @if($post->status === 'draft')
                            <div id="agent-post-edit-modal-{{ $post->id }}" class="agent-post-modal fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
                                <div class="bg-white w-full max-w-4xl rounded-2xl shadow-xl p-6 max-h-[88vh] overflow-y-auto relative">
                                    <button type="button" class="modal-close-btn absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                                    <h4 class="text-xl font-bold text-gray-800 mb-4">編輯代購貼文</h4>

                                    <form method="POST" action="{{ route('agent.posts.update', $post) }}" enctype="multipart/form-data" class="agent-post-edit-form space-y-5" data-max-items="5">
                                        @csrf
                                        @method('PATCH')

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">貼文標題</label>
                                                <input type="text" name="title" value="{{ $post->title }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">代購國家</label>
                                                <select name="country" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                                    @foreach (['日本', '韓國', '美國', '英國'] as $country)
                                                        <option value="{{ $country }}" @selected($post->country === $country)>{{ $country }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">銷售開始日</label>
                                                <input type="date" name="start_date" value="{{ optional($post->start_date)->format('Y-m-d') }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 mb-2">銷售結束日</label>
                                                <input type="date" name="end_date" value="{{ optional($post->end_date)->format('Y-m-d') }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">描述訊息</label>
                                            <textarea name="description" rows="4" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>{{ $post->description }}</textarea>
                                        </div>

                                        <div class="border-t pt-4">
                                            <div class="flex items-center justify-between mb-4">
                                                <h5 class="font-bold text-gray-800">商品規格</h5>
                                                <button type="button" class="edit-add-product-btn inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 text-sm font-semibold hover:bg-indigo-100">
                                                    <i class="bi bi-plus-circle"></i> 新增商品
                                                </button>
                                            </div>
                                            <div class="edit-products-container space-y-4">
                                                @foreach($post->products as $pIndex => $product)
                                                    <div class="edit-product-item rounded-xl border border-gray-200 p-4" data-index="{{ $pIndex }}">
                                                        <div class="flex items-center justify-between mb-3">
                                                            <h6 class="font-semibold text-gray-700">商品 #{{ $pIndex + 1 }}</h6>
                                                            <button type="button" class="edit-remove-product-btn text-sm text-rose-600 hover:text-rose-700 font-semibold">刪除</button>
                                                        </div>
                                                        <input type="hidden" name="products[{{ $pIndex }}][id]" value="{{ $product->id }}">
                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                            <input type="text" name="products[{{ $pIndex }}][name]" value="{{ $product->name }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="商品名稱" required>
                                                            <input type="number" min="0" step="0.01" name="products[{{ $pIndex }}][price]" value="{{ $product->price }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="單價" required>
                                                            <input type="number" min="1" step="1" name="products[{{ $pIndex }}][max_quantity]" value="{{ $product->max_quantity }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="最高數量" required>
                                                        </div>
                                                        <div class="mt-3">
                                                             <div class="mb-2 h-24 w-24 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center text-xs text-gray-400">
                                                                @if($product->display_image_url)
                                                                    <img
                                                                        src="{{ $product->display_image_url }}"
                                                                        alt="{{ $product->name }}"
                                                                        class="edit-product-image-preview h-full w-full object-cover"
                                                                        data-original-src="{{ $product->display_image_url }}"
                                                                    >
                                                                @else
                                                                    <span class="edit-product-image-placeholder">尚無圖片</span>
                                                                @endif
                                                            </div>
                                                            <input type="file" accept="image/*" name="products[{{ $pIndex }}][image]" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-white file:text-indigo-700 file:font-semibold hover:file:bg-indigo-50">
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="flex justify-end">
                                            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition">儲存變更</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                               @endif
                        @endforeach
                     </section>

                     <template id="edit-product-template">
                        <div class="edit-product-item rounded-xl border border-gray-200 p-4" data-index="__INDEX__">
                            <div class="flex items-center justify-between mb-3">
                                <h6 class="font-semibold text-gray-700">商品 #__NUMBER__</h6>
                                <button type="button" class="edit-remove-product-btn text-sm text-rose-600 hover:text-rose-700 font-semibold">刪除</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <input type="text" name="products[__INDEX__][name]" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="商品名稱" required>
                                <input type="number" min="0" step="0.01" name="products[__INDEX__][price]" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="單價" required>
                                <input type="number" min="1" step="1" name="products[__INDEX__][max_quantity]" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="最高數量" required>
                            </div>
                            <div class="mt-3">
                                 <div class="mb-2 h-24 w-24 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center text-xs text-gray-400">
                                    <span class="edit-product-image-placeholder">尚無圖片</span>
                                </div>
                                <input type="file" accept="image/*" name="products[__INDEX__][image]" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-white file:text-indigo-700 file:font-semibold hover:file:bg-indigo-50">
                            </div>
                        </div>
                     </template>

                     <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            document.querySelectorAll('.agent-post-view-btn, .agent-post-edit-btn').forEach(function (btn) {
                                btn.addEventListener('click', function () {
                                    const modal = document.getElementById(btn.dataset.modalId);
                                    if (!modal) return;
                                    modal.classList.remove('hidden');
                                    modal.classList.add('flex');
                                });
                            });

                            document.querySelectorAll('.modal-close-btn').forEach(function (btn) {
                                btn.addEventListener('click', function () {
                                    const modal = btn.closest('.agent-post-modal');
                                    if (!modal) return;
                                    modal.classList.add('hidden');
                                    modal.classList.remove('flex');
                                });
                            });

                            document.querySelectorAll('.agent-post-modal').forEach(function (modal) {
                                modal.addEventListener('click', function (event) {
                                    if (event.target === modal) {
                                        modal.classList.add('hidden');
                                        modal.classList.remove('flex');
                                    }
                                });
                            });

                            const template = document.getElementById('edit-product-template').innerHTML;
                            document.querySelectorAll('.agent-post-edit-form').forEach(function (form) {
                                const container = form.querySelector('.edit-products-container');
                                const addBtn = form.querySelector('.edit-add-product-btn');
                                const maxItems = parseInt(form.dataset.maxItems, 10) || 5;
                                let nextIndex = Array.from(container.querySelectorAll('.edit-product-item')).reduce((max, item) => {
                                    const idx = parseInt(item.dataset.index, 10);
                                    return Number.isNaN(idx) ? max : Math.max(max, idx);
                                }, -1) + 1;

                                function refresh() {
                                    const items = container.querySelectorAll('.edit-product-item');
                                    const canDelete = items.length > 1;
                                    addBtn.disabled = items.length >= maxItems;
                                    addBtn.classList.toggle('opacity-50', addBtn.disabled);
                                    addBtn.classList.toggle('cursor-not-allowed', addBtn.disabled);
                                    items.forEach(function (item, i) {
                                        const title = item.querySelector('h6');
                                        if (title) title.textContent = `商品 #${i + 1}`;
                                        const removeBtn = item.querySelector('.edit-remove-product-btn');
                                        if (removeBtn) removeBtn.style.display = canDelete ? '' : 'none';
                                    });
                                }

                                function bindImagePreview(item) {
                                    const fileInput = item.querySelector('input[type="file"][name*="[image]"]');
                                    if (!fileInput || fileInput.dataset.previewBound === '1') return;

                                    fileInput.dataset.previewBound = '1';
                                    fileInput.addEventListener('change', function (event) {
                                        const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
                                        const wrapper = item.querySelector('.mb-2.h-24.w-24');
                                        if (!wrapper) return;

                                        if (!file) {
                                            const preview = wrapper.querySelector('.edit-product-image-preview');
                                            const originalSrc = preview?.dataset.originalSrc;
                                            if (originalSrc) {
                                                preview.src = originalSrc;
                                                return;
                                            }

                                            if (preview) {
                                                preview.remove();
                                            }
                                            if (!wrapper.querySelector('.edit-product-image-placeholder')) {
                                                wrapper.insertAdjacentHTML('beforeend', '<span class="edit-product-image-placeholder">尚無圖片</span>');
                                            }
                                            return;
                                        }

                                        const oldPlaceholder = wrapper.querySelector('.edit-product-image-placeholder');
                                        if (oldPlaceholder) oldPlaceholder.remove();

                                        let preview = wrapper.querySelector('.edit-product-image-preview');
                                        if (!preview) {
                                            preview = document.createElement('img');
                                            preview.className = 'edit-product-image-preview h-full w-full object-cover';
                                            wrapper.appendChild(preview);
                                        }

                                        preview.src = URL.createObjectURL(file);
                                    });
                                }

                                addBtn.addEventListener('click', function () {
                                    const count = container.querySelectorAll('.edit-product-item').length;
                                    if (count >= maxItems) return;
                                    const html = template.replaceAll('__INDEX__', String(nextIndex)).replaceAll('__NUMBER__', String(count + 1));
                                    container.insertAdjacentHTML('beforeend', html);
                                     const appendedItem = container.querySelector('.edit-product-item:last-child');
                                    if (appendedItem) {
                                        bindImagePreview(appendedItem);
                                    }
                                    nextIndex += 1;
                                    refresh();
                                });

                                container.addEventListener('click', function (event) {
                                    const removeBtn = event.target.closest('.edit-remove-product-btn');
                                    if (!removeBtn) return;
                                    if (container.querySelectorAll('.edit-product-item').length <= 1) return;
                                    const item = removeBtn.closest('.edit-product-item');
                                    if (item) item.remove();
                                    refresh();
                                });

                                refresh();
                                 container.querySelectorAll('.edit-product-item').forEach(function (item) {
                                    bindImagePreview(item);
                                });
                            });
                        });
                     </script>
                     
                     


                    </div>




                    <!-- 分頁二：訂單管理 -->
                    <div x-show="activeTab === 'order-management'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4">
                        <section id="order-management" class="bg-white rounded-2xl shadow-sm border border-indigo-100 p-6" x-data="{ orderTab: 'pending' }">
                            <h3 class="text-lg font-bold text-indigo-600 mb-4">請託單管理</h3>

                            {{-- 切換按鈕 --}}
                            <div class="flex gap-2 mb-6 border-b border-gray-100 pb-4">
                                <button type="button"
                                    @click="orderTab = 'pending'"
                                    :class="orderTab === 'pending' ? 'bg-red-50 text-red-700 border-red-300 font-bold' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'"
                                    class="px-4 py-1.5 rounded-full border text-xs transition">
                                    確認中
                                </button>
                                <button type="button"
                                    @click="orderTab = 'accepted'"
                                    :class="orderTab === 'accepted' ? 'bg-emerald-50 text-emerald-700 border-emerald-300 font-bold' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'"
                                    class="px-4 py-1.5 rounded-full border text-xs transition">
                                    已接單
                                </button>
                                <button type="button"
                                    @click="orderTab = 'rejected'"
                                    :class="orderTab === 'rejected' ? 'bg-gray-100 text-gray-700 border-gray-300 font-bold' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'"
                                    class="px-4 py-1.5 rounded-full border text-xs transition">
                                    已拒絕
                                </button>
                                <button type="button"
                                    @click="orderTab = 'shipped'"
                                    :class="orderTab === 'shipped' ? 'bg-blue-50 text-blue-700 border-blue-300 font-bold' : 'bg-white text-gray-500 border-gray-200 hover:bg-gray-50'"
                                    class="px-4 py-1.5 rounded-full border text-xs transition">
                                    已出貨
                                </button>
                            </div>

                            @php
                                // 一次查出所有 quote，避免 N+1
                                $allMyQuotes = \App\Models\Quote::with([
                                        'requestList.user:id,name',
                                        'requestList.items:id,request_list_id,name,quantity',
                                        'quoteItems:id,quote_id,request_item_id,unit_price'
                                    ])
                                    ->where('user_id', Auth::id())
                                    ->latest('updated_at')
                                    ->get();

                                $pendingQuotes  = $allMyQuotes->whereIn('status', ['pending', 'returned'])->values();
                                $acceptedQuotes = $allMyQuotes->where('status', 'accepted')->values();
                                $rejectedQuotes = $allMyQuotes->where('status', 'rejected')->values();
                                $shippedQuotes  = $allMyQuotes->whereIn('status', ['shipped', 'arrivaled'])->reject(fn($q) => $q->status === 'completed')->values();

                                $myQuotes = $pendingQuotes; // 保留相容性

                                // 一次查出所有相關 request_list_id
                                $allRequestListIds = $allMyQuotes->pluck('request_list_id')->unique()->filter()->values()->all();

                                // 一次查出所有聊天訊息，依 request_list_id 分組，避免 N+1
                                $myAgentId = Auth::id();
                                $allChatMessagesGrouped = \App\Models\Message::with(['sender:id,name'])
                                    ->whereIn('request_list_id', $allRequestListIds)
                                    ->where(function ($q) use ($myAgentId) {
                                        $q->where('sender_id', $myAgentId)
                                          ->orWhere('receiver_id', $myAgentId);
                                    })
                                    ->orderBy('created_at')
                                    ->get()
                                    ->groupBy('request_list_id');

                                $statusLabelMap = [
                                    'pending'   => '請託人確認中...',
                                    'accepted'  => '已接單',
                                    'rejected'  => '已拒絕',
                                    'shipped'   => '已出貨',
                                    'matched'   => '已配對成功',
                                    'completed' => '已完成',
                                ];

                                $statusClassMap = [
                                    'pending'   => 'bg-red-50 text-red-700 border-red-200',
                                    'accepted'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'rejected'  => 'bg-gray-100 text-gray-600 border-gray-200',
                                    'shipped'   => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'matched'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'completed' => 'bg-slate-100 text-slate-700 border-slate-200',
                                ];
                            @endphp

                            {{-- 確認中 --}}
                            <div x-show="orderTab === 'pending'" class="space-y-4">
                                @php
                                    // returned 優先排在前面
                                    $sortedPendingQuotes = $pendingQuotes->sortByDesc(fn($q) => $q->status === 'returned' ? 1 : 0)->values();
                                @endphp
                                @forelse($sortedPendingQuotes as $quote)
                                    @php
                                        $requestList = $quote->requestList;
                                        if (!$requestList) continue;
                                        $firstItem = $requestList->items->first();
                                        $isReturned = $quote->status === 'returned';
                                        // 狀態標籤
                                        if ($isReturned) {
                                            $pendingLabel = '已退回';
                                            $pendingBadgeClass = 'bg-orange-50 text-orange-600 border-orange-200';
                                            $cardBorderClass = 'border-orange-100 bg-orange-50/30';
                                        } else {
                                            $pendingLabel = '未接受';
                                            $pendingBadgeClass = 'bg-indigo-50 text-indigo-600 border-indigo-200';
                                            $cardBorderClass = 'border-indigo-100 bg-indigo-50/40';
                                        }
                                    @endphp
                                    <div class="rounded-xl border {{ $cardBorderClass }} p-4">
                                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="text-base font-bold text-gray-800 truncate">{{ $requestList->title }}</h4>
                                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-bold {{ $pendingBadgeClass }}">{{ $pendingLabel }}</span>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    請託人：{{ $requestList->user->name ?? '未知會員' }} ・
                                                    截止日：{{ optional($requestList->deadline)->format('Y-m-d') ?? '-' }}
                                                </p>
                                                @if($firstItem)
                                                    <p class="mt-2 text-sm text-gray-700">
                                                        商品：{{ $firstItem->name }} × {{ (int) $firstItem->quantity }}
                                                        @if($requestList->items->count() > 1)
                                                            <span class="text-xs text-gray-500">（另有 {{ $requestList->items->count() - 1 }} 項）</span>
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="button"
                                                   onclick="document.getElementById('view-rl-{{ $quote->id }}').classList.remove('hidden');document.getElementById('view-rl-{{ $quote->id }}').classList.add('flex');"
                                                   class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-200 transition">
                                                    檢視
                                                </button>
                                                <button type="button"
                                                   onclick="openAgentRequestChatModal({{ $requestList->id }})"
                                                   class="relative inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white hover:bg-indigo-700 transition">
                                                    聊天
                                                    <span class="agent-chat-badge hidden absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center"
                                                          data-request-list-id="{{ $requestList->id }}"></span>
                                                </button>
                                                @if($isReturned)
                                                    <button type="button"
                                                        onclick="document.getElementById('edit-quote-modal-{{ $quote->id }}').classList.remove('hidden');document.getElementById('edit-quote-modal-{{ $quote->id }}').classList.add('flex');"
                                                        class="inline-flex items-center rounded-lg bg-orange-500 px-3 py-2 text-xs font-bold text-white hover:bg-orange-600 transition">
                                                        修改
                                                    </button>
                                                @else
                                                    <button type="button" disabled
                                                        class="inline-flex items-center rounded-lg bg-gray-200 px-3 py-2 text-xs font-bold text-gray-400 cursor-not-allowed">
                                                        修改
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 修改報價 Modal（只有 returned 時才有意義） --}}
                                    <div id="edit-quote-modal-{{ $quote->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
     onclick="if(event.target===this){this.classList.add('hidden');this.classList.remove('flex');}">
    
    <div class="bg-white w-full max-w-md rounded-3xl shadow-xl p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h4 class="text-xl font-bold text-gray-800">修改報價</h4>
            <button type="button" class="text-2xl text-gray-400 hover:text-gray-600"
                    onclick="document.getElementById('edit-quote-modal-{{ $quote->id }}').classList.add('hidden');document.getElementById('edit-quote-modal-{{ $quote->id }}').classList.remove('flex');">&times;</button>
        </div>

        <form method="POST" action="{{ route('quotes.update', $quote->id) }}">
            @csrf
            @method('PATCH')
            
            <div class="space-y-6">
               @php
                    $quoteItemsByRequestItemId = $quote->quoteItems->keyBy('request_item_id');
                @endphp
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-gray-500">商品單價（可修改）</label>
                    @foreach($requestList->items as $item)
                        @php
                            $unitPrice = $quoteItemsByRequestItemId->get($item->id)?->unit_price ?? 0;
                        @endphp
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">{{ $item->name }}</p>
                                    <p class="text-xs text-gray-500">數量：{{ (int) $item->quantity }}</p>
                                </div>
                                <div class="sm:w-44">
                                    <input type="number" name="items[{{ $item->id }}][agent_quote]" min="0" step="1"
                                        value="{{ old('items.'.$item->id.'.agent_quote', $unitPrice) }}"
                                        class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- 預計到貨日 --}}
                <div>
                    <label class="flex items-center text-xs font-bold text-gray-500 mb-1 gap-1">
                        <i class="bi bi-calendar-check"></i> 預計到貨日
                    </label>
                    <input type="date" name="estimated_date"
                           value="{{ optional($quote->estimated_date)->format('Y-m-d') }}"
                           max="{{ optional($requestList->deadline)->format('Y-m-d') }}"
                           class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                           required>
                </div>

                {{-- 報價備註 --}}
                <div>
                    <label class="flex items-center text-xs font-bold text-gray-500 mb-1 gap-1">
                        <i class="bi bi-chat-left-text"></i> 報價備註（選填）
                    </label>
                    <textarea name="comment" rows="2"
                              class="w-full rounded-2xl border border-gray-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="補充說明...">{{ $quote->comment }}</textarea>
                </div>

                {{-- 🎯 底部按鈕區 (改為左右佈局) --}}
                <div class="grid grid-cols-2 gap-4 pt-2">
                    <button type="button"
                            onclick="document.getElementById('edit-quote-modal-{{ $quote->id }}').classList.add('hidden');document.getElementById('edit-quote-modal-{{ $quote->id }}').classList.remove('flex');"
                            class="rounded-2xl bg-gray-100 py-3 text-sm font-bold text-gray-700 hover:bg-gray-200 transition">
                        取消
                    </button>
                    <button type="submit"
                            class="rounded-2xl bg-indigo-500 py-3 text-sm font-bold text-white hover:bg-indigo-600 transition shadow-lg shadow-indigo-200">
                         確認送出
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
                                    {{-- 檢視完整請託單 Modal --}}
                                    <div id="view-rl-{{ $quote->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
                                         onclick="if(event.target===this){this.classList.add('hidden');this.classList.remove('flex');}">
                                        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl max-h-[88vh] overflow-y-auto">
                                            <div class="flex items-center justify-between border-b px-6 py-4">
                                                <h4 class="text-lg font-bold text-gray-800">{{ $requestList->title }}</h4>
                                                <button type="button" onclick="document.getElementById('view-rl-{{ $quote->id }}').classList.add('hidden');document.getElementById('view-rl-{{ $quote->id }}').classList.remove('flex');" class="text-2xl text-gray-400 hover:text-gray-600">&times;</button>
                                            </div>
                                            <div class="px-6 py-5 space-y-4">
                                                <div class="grid grid-cols-2 gap-3 text-sm">
                                                    <div><span class="text-gray-400 text-xs">請託人</span><p class="font-semibold text-gray-800 mt-0.5">{{ $requestList->user->name ?? '-' }}</p></div>
                                                    <div><span class="text-gray-400 text-xs">代購國家</span><p class="font-semibold text-gray-800 mt-0.5">{{ $requestList->country ?? '-' }}</p></div>
                                                    <div><span class="text-gray-400 text-xs">截止日期</span><p class="font-semibold text-gray-800 mt-0.5">{{ optional($requestList->deadline)->format('Y-m-d') ?? '-' }}</p></div>
                                                    <div><span class="text-gray-400 text-xs">報價金額</span><p class="font-semibold text-indigo-700 mt-0.5">NT$ {{ number_format((float)($quote->price ?? 0), 0) }}</p></div>
                                                    @if($requestList->store_name)
                                                    <div><span class="text-gray-400 text-xs">指定店家</span><p class="font-semibold text-gray-800 mt-0.5">{{ $requestList->store_name }}</p></div>
                                                    @endif
                                                    @if($requestList->detail_address)
                                                    <div class="col-span-2"><span class="text-gray-400 text-xs">詳細地址</span><p class="font-semibold text-gray-800 mt-0.5">{{ $requestList->detail_address }}</p></div>
                                                    @endif
                                                </div>
                                                @if($requestList->note)
                                                <div class="bg-amber-50 border border-amber-100 rounded-xl p-3">
                                                    <p class="text-xs text-amber-600 font-bold mb-1">備註</p>
                                                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $requestList->note }}</p>
                                                </div>
                                                @endif
                                                <div>
                                                    <p class="text-sm font-bold text-gray-700 mb-3">商品清單（{{ $requestList->items->count() }} 項）</p>
                                                    <div class="space-y-3">
                                                        @foreach($requestList->items as $item)
                                                        <div class="rounded-xl border border-gray-100 p-3 flex gap-3">
                                                            <div class="w-20 h-20 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                                                                @if($item->reference_image)
                                                                    <img src="{{ route('request-item.image', ['requestItem' => $item->id, 'v' => $item->updated_at?->timestamp ?? now()->timestamp]) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                                                @else
                                                                    <i class="bi bi-image text-2xl text-gray-300"></i>
                                                                @endif
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="font-semibold text-gray-800 text-sm">{{ $item->name }}</p>
                                                                <p class="text-xs text-gray-500 mt-0.5">數量：{{ (int) $item->quantity }} 件</p>
                                                                @if($item->expected_price)
                                                                    <p class="text-xs text-gray-500">期望單價：{{ $requestList->currency ?? 'NT$' }} {{ number_format((float)$item->expected_price, 0) }}</p>
                                                                @endif
                                                                @if($item->specification)
                                                                    <p class="text-xs text-gray-500 mt-1">規格：{{ $item->specification }}</p>
                                                                @endif
                                                                @if($item->reference_url)
                                                                    <a href="{{ $item->reference_url }}" target="_blank" class="text-xs text-indigo-500 hover:underline mt-1 inline-block">參考連結 →</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        // 直接從已分組的資料取，不再打資料庫
                                        $agentChatMessages = $allChatMessagesGrouped->get($requestList->id, collect());
                                    @endphp
                                    <div id="agent-request-chat-modal-{{ $requestList->id }}" class="agent-request-chat-modal fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="handleAgentRequestChatBackdrop(event, {{ $requestList->id }})">
                                        <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                                            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                                                <div>
                                                    <h4 class="text-lg font-bold text-gray-800">{{ $requestList->title }}</h4>
                                                    <p class="text-xs text-gray-500 mt-0.5">請託人：{{ $requestList->user->name ?? '使用者' }}</p>
                                                </div>
                                                <button type="button" class="text-2xl text-gray-500 hover:text-gray-700" onclick="closeAgentRequestChatModal({{ $requestList->id }})" aria-label="關閉聊天室">✕</button>
                                            </div>

                                            
                                            @php
                                                $agentQuoteItems = $quote->quoteItems->keyBy('request_item_id');
                                            @endphp
                                            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3 text-sm text-gray-700">
                                                @if($quote->quoteItems->isNotEmpty())
                                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                                        <span class="font-semibold text-gray-600">我的報價明細</span>
                                                        <span class="text-xs text-gray-400">{{ optional($quote->created_at)->format('Y-m-d H:i') }}</span>
                                                    </div>
                                                    @foreach($requestList->items as $item)
                                                        @php
                                                            $qItem = $agentQuoteItems->get($item->id);
                                                            $unitPrice = $qItem?->unit_price;
                                                            $subtotal = $unitPrice !== null ? $unitPrice * $item->quantity : null;
                                                        @endphp
                                                        <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                                                            <span class="text-gray-700">{{ $item->name }} × {{ $item->quantity }}</span>
                                                            <span class="text-gray-500 text-xs">
                                                                @if($unitPrice !== null)
                                                                    NT${{ number_format($unitPrice, 0) }} × {{ $item->quantity }} =
                                                                    <span class="font-semibold text-indigo-600">NT${{ number_format($subtotal, 0) }}</span>
                                                                @else
                                                                    <span class="text-gray-400">未填寫</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                    <div class="mt-2 flex justify-end">
                                                        <span class="text-xs text-gray-500">總價：</span>
                                                        <span class="ml-1 font-bold text-indigo-600">
                                                            NT${{ number_format((float)($quote->price ?? $quote->agent_quote_total ?? 0), 0) }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">尚未填寫報價明細</span>
                                                @endif
                                            </div>

                                            
                                            <div id="agent-request-chat-messages-{{ $requestList->id }}" class="max-h-[45vh] overflow-y-auto bg-gray-50 px-5 py-4">
                                                @forelse($agentChatMessages as $agentMsg)
                                                    @php $isMine = (int) $agentMsg->sender_id === (int) auth()->id(); @endphp
                                                    <div class="mb-3 flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                                        <div class="max-w-[75%]">
                                                            <div class="rounded-xl border px-3 py-2 {{ $isMine ? 'bg-indigo-100 border-indigo-200' : 'bg-white border-gray-200' }}">
                                                                <p class="text-xs text-gray-500">{{ $agentMsg->sender->name ?? '使用者' }}</p>
                                                                <p class="mt-1 text-sm text-gray-800 break-words">{{ $agentMsg->body }}</p>
                                                            </div>
                                                            <p class="mt-1 text-xs text-gray-500 flex items-center gap-1 {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                                                <span>{{ optional($agentMsg->created_at)->format('Y-m-d H:i') }}</span>
                                                                @if($isMine)
                                                                    <span class="agent-msg-read-status" style="color: {{ $agentMsg->read_at ? '#6366f1' : '#94a3b8' }}">
                                                                        {{ $agentMsg->read_at ? '已讀' : '未讀' }}
                                                                    </span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="agent-request-chat-empty py-12 text-center text-sm text-gray-400" id="agent-empty-tip-{{ $requestList->id }}">目前尚無訊息，開始第一句對話吧。</p>
                                                @endforelse
                                            </div>

                                            
                                            <div class="flex items-center gap-2 border-t border-gray-200 px-4 py-3">
                                                <input type="text"
                                                    class="agent-request-chat-input w-full rounded-full border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="輸入訊息..."
                                                    maxlength="2000"
                                                    data-request-list-id="{{ $requestList->id }}"
                                                    data-receiver-id="{{ $requestList->user_id }}"
                                                    data-send-url="{{ route('request-list.chat.send', $requestList) }}">
                                                <button type="button"
                                                    class="agent-request-chat-send-btn rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                                                    data-request-list-id="{{ $requestList->id }}">送出</button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-gray-400 text-sm text-center py-8 border border-dashed border-indigo-200 rounded-xl">
                                        目前沒有確認中的報價。
                                    </div>
                                @endforelse
                            </div>

                            {{-- 已接單 --}}
                            <div x-show="orderTab === 'accepted'" class="space-y-4">
                                @forelse($acceptedQuotes as $quote)
                                    @php
                                        $requestList = $quote->requestList;
                                        if (!$requestList) continue;
                                        $firstItem = $requestList->items->first();
                                    @endphp
                                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-4">
                                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="text-base font-bold text-gray-800 truncate">{{ $requestList->title }}</h4>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    請託人：{{ $requestList->user->name ?? '未知會員' }} ・
                                                    截止日：{{ optional($requestList->deadline)->format('Y-m-d') ?? '-' }}
                                                </p>
                                                @if($firstItem)
                                                    <p class="mt-2 text-sm text-gray-700">
                                                        商品：{{ $firstItem->name }} × {{ (int) $firstItem->quantity }}
                                                        @if($requestList->items->count() > 1)
                                                            <span class="text-xs text-gray-500">（另有 {{ $requestList->items->count() - 1 }} 項）</span>
                                                        @endif
                                                    </p>
                                                @endif
                                                
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="button"
                                                    onclick="document.getElementById('view-modal-accepted-{{ $quote->id }}').classList.remove('hidden'); document.getElementById('view-modal-accepted-{{ $quote->id }}').classList.add('flex');"
                                                    class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-200 transition">
                                                    檢視
                                                </button>
                                                <button type="button"
                                                   onclick="openAgentRequestChatModal({{ $requestList->id }})"
                                                   class="relative inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white hover:bg-indigo-700 transition">
                                                    聊天
                                                    <span class="agent-chat-badge hidden absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center"
                                                          data-request-list-id="{{ $requestList->id }}"></span>
                                                </button>
                                                <form method="POST" action="{{ route('quotes.ship', $quote->id) }}" onsubmit="return confirm('確認將此訂單標記為已出貨？')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                                                        出貨
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="view-modal-accepted-{{ $quote->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
                                         onclick="if(event.target===this){this.classList.add('hidden');this.classList.remove('flex');}">
                                        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl max-h-[88vh] overflow-y-auto relative">
                                            <div class="flex items-center justify-between border-b px-6 py-4">
                                                <h4 class="text-lg font-bold text-gray-800">{{ $requestList->title }}</h4>
                                                <button type="button" class="text-2xl text-gray-400 hover:text-gray-600"
                                                    onclick="document.getElementById('view-modal-accepted-{{ $quote->id }}').classList.add('hidden');document.getElementById('view-modal-accepted-{{ $quote->id }}').classList.remove('flex');">&times;</button>
                                            </div>
                                            <div class="px-6 py-4 space-y-4">
                                                <div class="grid grid-cols-2 gap-3 text-sm">
                                                    <div><span class="text-gray-400">請託人</span><p class="font-semibold text-gray-800">{{ $requestList->user->name ?? '-' }}</p></div>
                                                    <div><span class="text-gray-400">國家／地區</span><p class="font-semibold text-gray-800">{{ $requestList->country ?? '-' }}</p></div>
                                                    <div><span class="text-gray-400">店家名稱</span><p class="font-semibold text-gray-800">{{ $requestList->store_name ?? '-' }}</p></div>
                                                    <div><span class="text-gray-400">截止日</span><p class="font-semibold text-gray-800">{{ optional($requestList->deadline)->format('Y-m-d') ?? '-' }}</p></div>
                                                    <div><span class="text-gray-400">報價金額</span><p class="font-semibold text-indigo-700">NT$ {{ number_format((float)($quote->price ?? 0), 0) }}</p></div>
                                                    <div><span class="text-gray-400">收件地址</span><p class="font-semibold text-gray-800">{{ $requestList->detail_address ?? '-' }}</p></div>
                                                </div>
                                                @if($requestList->note)
                                                    <div class="bg-amber-50 border border-amber-100 rounded-xl px-4 py-3">
                                                        <p class="text-xs text-amber-600 font-bold mb-1">備註</p>
                                                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $requestList->note }}</p>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h5 class="font-bold text-gray-800 mb-3 text-sm">商品清單</h5>
                                                    <div class="space-y-3">
                                                        @foreach($requestList->items as $item)
                                                            <div class="rounded-xl border border-gray-100 p-3 flex gap-4 items-start">
                                                                @if($item->reference_image)
                                                                    <img src="{{ route('request-item.image', ['requestItem' => $item->id, 'v' => $item->updated_at?->timestamp ?? now()->timestamp]) }}" alt="{{ $item->name }}" class="w-20 h-20 rounded-lg object-cover border border-gray-100 flex-shrink-0">
                                                                @else
                                                                    <div class="w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center text-gray-300 flex-shrink-0"><i class="bi bi-image text-2xl"></i></div>
                                                                @endif
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="font-semibold text-gray-800">{{ $item->name }}</p>
                                                                    <p class="text-xs text-gray-500 mt-0.5">數量：{{ (int)$item->quantity }}</p>
                                                                    @if($item->expected_price)<p class="text-xs text-gray-500">期望單價：NT$ {{ number_format((float)$item->expected_price, 0) }}</p>@endif
                                                                    @if($item->specification)<p class="text-xs text-gray-500">規格：{{ $item->specification }}</p>@endif
                                                                    @if($item->reference_url)<a href="{{ $item->reference_url }}" target="_blank" class="text-xs text-indigo-500 hover:underline mt-1 inline-block">參考連結 →</a>@endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $agentChatMessages = $allChatMessagesGrouped->get($requestList->id, collect());
                                    @endphp
                                    <div id="agent-request-chat-modal-{{ $requestList->id }}" class="agent-request-chat-modal fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="handleAgentRequestChatBackdrop(event, {{ $requestList->id }})">
                                        <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                                            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                                                <div>
                                                    <h4 class="text-lg font-bold text-gray-800">{{ $requestList->title }}</h4>
                                                    <p class="text-xs text-gray-500 mt-0.5">請託人：{{ $requestList->user->name ?? '使用者' }}</p>
                                                </div>
                                                <button type="button" class="text-2xl text-gray-500 hover:text-gray-700" onclick="closeAgentRequestChatModal({{ $requestList->id }})" aria-label="關閉聊天室">✕</button>
                                            </div>
                                            @php
                                                $agentQuoteItems = $quote->quoteItems->keyBy('request_item_id');
                                            @endphp
                                            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3 text-sm text-gray-700">
                                                @if($quote->quoteItems->isNotEmpty())
                                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                                        <span class="font-semibold text-gray-600">我的報價明細</span>
                                                        <span class="text-xs text-gray-400">{{ optional($quote->created_at)->format('Y-m-d H:i') }}</span>
                                                    </div>
                                                    @foreach($requestList->items as $item)
                                                        @php
                                                            $qItem = $agentQuoteItems->get($item->id);
                                                            $unitPrice = $qItem?->unit_price;
                                                            $subtotal = $unitPrice !== null ? $unitPrice * $item->quantity : null;
                                                        @endphp
                                                        <div class="flex items-center justify-between py-1 border-b border-gray-100 last:border-0">
                                                            <span class="text-gray-700">{{ $item->name }} × {{ $item->quantity }}</span>
                                                            <span class="text-gray-500 text-xs">
                                                                @if($unitPrice !== null)
                                                                    NT${{ number_format($unitPrice, 0) }} × {{ $item->quantity }} =
                                                                    <span class="font-semibold text-indigo-600">NT${{ number_format($subtotal, 0) }}</span>
                                                                @else
                                                                    <span class="text-gray-400">未填寫</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                    <div class="mt-2 flex justify-end">
                                                        <span class="text-xs text-gray-500">總價：</span>
                                                        <span class="ml-1 font-bold text-indigo-600">
                                                            NT${{ number_format((float)($quote->price ?? $quote->agent_quote_total ?? 0), 0) }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">尚未填寫報價明細</span>
                                                @endif
                                            </div>
                                            <div id="agent-request-chat-messages-{{ $requestList->id }}" class="max-h-[45vh] overflow-y-auto bg-gray-50 px-5 py-4">
                                                @forelse($agentChatMessages as $agentMsg)
                                                    @php $isMine = (int) $agentMsg->sender_id === (int) auth()->id(); @endphp
                                                    <div class="mb-3 flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                                        <div class="max-w-[75%]">
                                                            <div class="rounded-xl border px-3 py-2 {{ $isMine ? 'bg-indigo-100 border-indigo-200' : 'bg-white border-gray-200' }}">
                                                                <p class="text-xs text-gray-500">{{ $agentMsg->sender->name ?? '使用者' }}</p>
                                                                <p class="mt-1 text-sm text-gray-800 break-words">{{ $agentMsg->body }}</p>
                                                            </div>
                                                            <p class="mt-1 text-xs text-gray-500 flex items-center gap-1 {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                                                <span>{{ optional($agentMsg->created_at)->format('Y-m-d H:i') }}</span>
                                                                @if($isMine)
                                                                    <span class="agent-msg-read-status" style="color: {{ $agentMsg->read_at ? '#6366f1' : '#94a3b8' }}">
                                                                        {{ $agentMsg->read_at ? '已讀' : '未讀' }}
                                                                    </span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="agent-request-chat-empty py-12 text-center text-sm text-gray-400" id="agent-empty-tip-{{ $requestList->id }}">目前尚無訊息，開始第一句對話吧。</p>
                                                @endforelse
                                            </div>
                                            <div class="flex items-center gap-2 border-t border-gray-200 px-4 py-3">
                                                <input type="text"
                                                    class="agent-request-chat-input w-full rounded-full border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="輸入訊息..."
                                                    maxlength="2000"
                                                    data-request-list-id="{{ $requestList->id }}"
                                                    data-receiver-id="{{ $requestList->user_id }}"
                                                    data-send-url="{{ route('request-list.chat.send', $requestList) }}">
                                                <button type="button"
                                                    class="agent-request-chat-send-btn rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                                                    data-request-list-id="{{ $requestList->id }}">送出</button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-gray-400 text-sm text-center py-8 border border-dashed border-emerald-200 rounded-xl">
                                        目前沒有已接單的訂單。
                                    </div>
                                @endforelse
                            </div>

                            {{-- 已拒絕 --}}
                            <div x-show="orderTab === 'rejected'" class="space-y-4">
                                @forelse($rejectedQuotes as $quote)
                                    @php
                                        $requestList = $quote->requestList;
                                        if (!$requestList) continue;
                                        $firstItem = $requestList->items->first();
                                    @endphp
                                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="text-base font-bold text-gray-800 truncate">{{ $requestList->title }}</h4>
                                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-bold bg-gray-100 text-gray-600 border-gray-200">已拒絕</span>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    請託人：{{ $requestList->user->name ?? '未知會員' }} ・
                                                    截止日：{{ optional($requestList->deadline)->format('Y-m-d') ?? '-' }}
                                                </p>
                                                @if($firstItem)
                                                    <p class="mt-2 text-sm text-gray-700">
                                                        商品：{{ $firstItem->name }} × {{ (int) $firstItem->quantity }}
                                                        @if($requestList->items->count() > 1)
                                                            <span class="text-xs text-gray-500">（另有 {{ $requestList->items->count() - 1 }} 項）</span>
                                                        @endif
                                                    </p>
                                                @endif
                                                
                                                <p class="mt-1 text-xs text-gray-400">被拒絕後仍可重新報價，前提是請託單尚未被其他代購人接走。</p>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="button"
                                                    onclick="document.getElementById('view-modal-rejected-{{ $quote->id }}').classList.remove('hidden'); document.getElementById('view-modal-rejected-{{ $quote->id }}').classList.add('flex');"
                                                    class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-200 transition">
                                                    檢視
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="view-modal-rejected-{{ $quote->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
                                         onclick="if(event.target===this){this.classList.add('hidden');this.classList.remove('flex');}">
                                        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl max-h-[88vh] overflow-y-auto relative">
                                            <div class="flex items-center justify-between border-b px-6 py-4">
                                                <h4 class="text-lg font-bold text-gray-800">{{ $requestList->title }}</h4>
                                                <button type="button" class="text-2xl text-gray-400 hover:text-gray-600"
                                                    onclick="document.getElementById('view-modal-rejected-{{ $quote->id }}').classList.add('hidden');document.getElementById('view-modal-rejected-{{ $quote->id }}').classList.remove('flex');">&times;</button>
                                            </div>
                                            <div class="px-6 py-4 space-y-4">
                                                <div class="grid grid-cols-2 gap-3 text-sm">
                                                    <div><span class="text-gray-400">請託人</span><p class="font-semibold text-gray-800">{{ $requestList->user->name ?? '-' }}</p></div>
                                                    <div><span class="text-gray-400">國家／地區</span><p class="font-semibold text-gray-800">{{ $requestList->country ?? '-' }}</p></div>
                                                    <div><span class="text-gray-400">店家名稱</span><p class="font-semibold text-gray-800">{{ $requestList->store_name ?? '-' }}</p></div>
                                                    <div><span class="text-gray-400">截止日</span><p class="font-semibold text-gray-800">{{ optional($requestList->deadline)->format('Y-m-d') ?? '-' }}</p></div>
                                                    <div><span class="text-gray-400">報價金額</span><p class="font-semibold text-indigo-700">NT$ {{ number_format((float)($quote->price ?? 0), 0) }}</p></div>
                                                    <div><span class="text-gray-400">收件地址</span><p class="font-semibold text-gray-800">{{ $requestList->detail_address ?? '-' }}</p></div>
                                                </div>
                                                @if($requestList->note)
                                                    <div class="bg-amber-50 border border-amber-100 rounded-xl px-4 py-3">
                                                        <p class="text-xs text-amber-600 font-bold mb-1">備註</p>
                                                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $requestList->note }}</p>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h5 class="font-bold text-gray-800 mb-3 text-sm">商品清單</h5>
                                                    <div class="space-y-3">
                                                        @foreach($requestList->items as $item)
                                                            <div class="rounded-xl border border-gray-100 p-3 flex gap-4 items-start">
                                                                @if($item->reference_image)
                                                                    <img src="{{ route('request-item.image', ['requestItem' => $item->id, 'v' => $item->updated_at?->timestamp ?? now()->timestamp]) }}" alt="{{ $item->name }}" class="w-20 h-20 rounded-lg object-cover border border-gray-100 flex-shrink-0">
                                                                @else
                                                                    <div class="w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center text-gray-300 flex-shrink-0"><i class="bi bi-image text-2xl"></i></div>
                                                                @endif
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="font-semibold text-gray-800">{{ $item->name }}</p>
                                                                    <p class="text-xs text-gray-500 mt-0.5">數量：{{ (int)$item->quantity }}</p>
                                                                    @if($item->expected_price)<p class="text-xs text-gray-500">期望單價：NT$ {{ number_format((float)$item->expected_price, 0) }}</p>@endif
                                                                    @if($item->specification)<p class="text-xs text-gray-500">規格：{{ $item->specification }}</p>@endif
                                                                    @if($item->reference_url)<a href="{{ $item->reference_url }}" target="_blank" class="text-xs text-indigo-500 hover:underline mt-1 inline-block">參考連結 →</a>@endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-gray-400 text-sm text-center py-8 border border-dashed border-gray-200 rounded-xl">
                                        目前沒有已拒絕的報價。
                                    </div>
                                @endforelse
                            </div>

                            {{-- 已出貨 --}}
                            <div x-show="orderTab === 'shipped'" class="space-y-4">
                                @forelse($shippedQuotes as $quote)
                                    @php
                                        $requestList = $quote->requestList;
                                        if (!$requestList) continue;
                                        $firstItem = $requestList->items->first();
                                        $statusLabel = $quote->status === 'arrivaled' ? '已到貨' : '已出貨';
                                        $statusClasses = $quote->status === 'arrivaled'
                                            ? 'bg-indigo-50 text-indigo-700 border-indigo-200'
                                            : 'bg-blue-50 text-blue-700 border-blue-200';
                                    @endphp
                                    <div class="rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="text-base font-bold text-gray-800 truncate">{{ $requestList->title }}</h4>
                                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-bold {{ $statusClasses }}">{{ $statusLabel }}</span>
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    請託人：{{ $requestList->user->name ?? '未知會員' }} ・
                                                    截止日：{{ optional($requestList->deadline)->format('Y-m-d') ?? '-' }}
                                                </p>
                                                @if($firstItem)
                                                    <p class="mt-2 text-sm text-gray-700">
                                                        商品：{{ $firstItem->name }} × {{ (int) $firstItem->quantity }}
                                                        @if($requestList->items->count() > 1)
                                                            <span class="text-xs text-gray-500">（另有 {{ $requestList->items->count() - 1 }} 項）</span>
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button type="button"
                                                   onclick="openAgentRequestChatModal({{ $requestList->id }})"
                                                   class="relative inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white hover:bg-indigo-700 transition">
                                                    聊天
                                                    <span class="agent-chat-badge hidden absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center"
                                                          data-request-list-id="{{ $requestList->id }}"></span>
                                                </button>
                                                @if($quote->status === 'arrivaled')
                                                    <button type="button" disabled
                                                        class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-2 text-xs font-bold text-gray-400 cursor-not-allowed">
                                                        到貨
                                                    </button>
                                                @else
                                                    <form method="POST" action="{{ route('quotes.arrive', $quote->id) }}" onsubmit="return confirm('確認將此訂單標記為已到貨？')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="inline-flex items-center rounded-lg bg-indigo-500 px-3 py-2 text-xs font-bold text-white hover:bg-indigo-600 transition">
                                                            到貨
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('quotes.complete', $quote->id) }}" onsubmit="return confirm('確認將此訂單標記為完成？')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition">
                                                        完成
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-gray-400 text-sm text-center py-8 border border-dashed border-gray-200 rounded-xl">
                                        目前沒有已出貨的訂單。
                                    </div>
                                @endforelse
                            </div>

                            {{-- 已出貨區：聊天 Modal --}}
                            @foreach($shippedQuotes as $quote)
                                @php $requestList = $quote->requestList; @endphp
                                @if($requestList)
                                <div id="agent-request-chat-modal-{{ $requestList->id }}" class="agent-request-chat-modal fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="handleAgentRequestChatBackdrop(event, {{ $requestList->id }})">
                                    <div class="relative flex w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" style="max-height: 88vh;">
                                        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                                            <div>
                                                <h4 class="text-lg font-bold text-gray-800">{{ $requestList->title }}</h4>
                                                <p class="text-xs text-gray-500 mt-0.5">請託人：{{ $requestList->user->name ?? '使用者' }}</p>
                                            </div>
                                            <button type="button" class="text-2xl text-gray-500 hover:text-gray-700" onclick="closeAgentRequestChatModal({{ $requestList->id }})" aria-label="關閉聊天室">✕</button>
                                        </div>
                                        @php
                                            $agentChatMessages = \App\Models\Message::query()
                                                ->where('request_list_id', $requestList->id)
                                                ->where(function ($q) use ($requestList) {
                                                    $q->where(function ($inner) use ($requestList) {
                                                        $inner->where('sender_id', Auth::id())
                                                              ->where('receiver_id', $requestList->user_id);
                                                    })->orWhere(function ($inner) use ($requestList) {
                                                        $inner->where('sender_id', $requestList->user_id)
                                                              ->where('receiver_id', Auth::id());
                                                    });
                                                })
                                                ->with(['sender:id,name'])
                                                ->orderBy('created_at')
                                                ->get();
                                        @endphp
                                        <div id="agent-request-chat-messages-{{ $requestList->id }}"
                                             class="flex-1 overflow-y-auto bg-gray-50 px-5 py-4"
                                             style="min-height: 300px; max-height: 55vh;">
                                            @forelse($agentChatMessages as $agentMsg)
                                                @php $isMine = (int) $agentMsg->sender_id === (int) Auth::id(); @endphp
                                                <div class="mb-3 flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                                    <div class="max-w-[75%]">
                                                        <div class="rounded-xl border px-3 py-2 {{ $isMine ? 'bg-indigo-100 border-indigo-200' : 'bg-white border-gray-200' }}">
                                                            <p class="text-xs text-gray-500">{{ $agentMsg->sender->name ?? '使用者' }}</p>
                                                            <p class="mt-1 text-sm text-gray-800 break-words">{{ $agentMsg->body }}</p>
                                                        </div>
                                                        <p class="mt-1 text-xs text-gray-500 flex items-center gap-1 {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                                            <span>{{ optional($agentMsg->created_at)->format('Y-m-d H:i') }}</span>
                                                            @if($isMine)
                                                                <span class="agent-msg-read-status" style="color:{{ $agentMsg->read_at ? '#6366f1' : '#94a3b8' }}">
                                                                    {{ $agentMsg->read_at ? '已讀' : '未讀' }}
                                                                </span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="agent-request-chat-empty py-12 text-center text-sm text-gray-400" id="agent-empty-tip-{{ $requestList->id }}">目前尚無訊息，開始第一句對話吧。</p>
                                            @endforelse
                                        </div>
                                        <div class="flex items-center gap-2 border-t border-gray-200 px-4 py-3 bg-white">
                                            <input type="text"
                                                class="agent-request-chat-input w-full rounded-full border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                placeholder="輸入訊息..."
                                                maxlength="2000"
                                                data-request-list-id="{{ $requestList->id }}"
                                                data-receiver-id="{{ $requestList->user_id }}"
                                                data-send-url="{{ route('request-list.chat.send', $requestList) }}">
                                            <button type="button"
                                                class="agent-request-chat-send-btn rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                                                data-request-list-id="{{ $requestList->id }}">送出</button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach

                        </section>
                    </div>

                    <!-- 分頁三：歷史紀錄 -->
                   <div x-show="activeTab === 'agent-history'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4">
    <section id="agent-history" class="bg-white rounded-2xl shadow-sm border border-amber-100 p-6" x-data="{ subTab: 'lists' }">
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h3 class="text-lg font-bold text-amber-600">代購歷史紀錄</h3>
                <p class="text-sm text-gray-500">查看您已完成的代購貼文團務與請購清單紀錄。</p>
            </div>
            
            <form method="GET" action="{{ route('agent.member') }}" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <input type="hidden" name="tab" value="agent-history">
                <input type="text" name="agent_history_search" value="{{ $agentHistorySearch ?? '' }}" placeholder="搜尋清單編號 / 貼文標題 / 買家"
                    class="w-full sm:w-72 rounded-xl border border-gray-200 px-4 py-2 text-sm text-gray-700 focus:border-amber-300 focus:ring-amber-200 focus:outline-none">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-500 text-white px-4 py-2 text-sm font-semibold hover:bg-amber-600 transition">
                    搜尋
                </button>
            </form>
        </div>

        <div class="flex border-b border-gray-100 mb-6 gap-2">
            <button @click="subTab = 'lists'" 
                :class="subTab === 'lists' ? 'border-amber-500 text-amber-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="pb-3 px-4 text-sm border-b-2 font-medium transition focus:outline-none">
                已接請購清單 ({{ $agentHistoryOrders->count() }})
            </button>
            <button @click="subTab = 'posts'" 
                :class="subTab === 'posts' ? 'border-amber-500 text-amber-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="pb-3 px-4 text-sm border-b-2 font-medium transition focus:outline-none">
                已完成代購貼文 ({{ $completedPosts->count() }})
            </button>
        </div>

        <div x-show="subTab === 'lists'" x-transition>
    @if($agentHistoryOrders->isEmpty())
        <div class="rounded-2xl border border-dashed border-amber-200 bg-amber-50/30 p-12 text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white text-amber-400 shadow-sm">
                <i class="bi bi-journal-x text-2xl"></i>
            </div>
            <p class="text-sm text-amber-700 font-semibold">尚未有完成的請購清單紀錄。</p>
            <p class="mt-1 text-xs text-amber-500">當您承接的請購清單順利結案後，相關明細將會呈現在這裡。</p>
        </div>
    @else
        <div class="grid gap-4">
            @foreach($agentHistoryOrders as $list)
                <article class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm hover:border-amber-200 transition">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="min-w-0">
                            <h4 class="text-base font-bold text-gray-800 truncate">{{ $list->title }}</h4>
                            <p class="text-xs text-gray-500 mt-1">
                                委託買家：{{ $list->user->name ?? '未知買家' }} ・ 結案日期：{{ $list->updated_at->format('Y-m-d') }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                {{ $list->status === 'completed' ? '已結案' : '處理中' }}
                            </span>
                            <span class="text-sm font-bold text-gray-700">NT$ {{ number_format((float)$list->agent_quote_total, 0) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-gray-600">
                        <div class="rounded-2xl bg-gray-50 p-3">
                            <p class="text-[11px] text-gray-400">代購目的地</p>
                            <p class="font-semibold text-gray-800 truncate mt-0.5">
                                {{ $list->country }}{{ $list->city ? ' - '.$list->city : '' }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-3">
                            <p class="text-[11px] text-gray-400">委託商品筆數</p>
                            <p class="font-semibold text-gray-800 mt-0.5">
                                {{ $list->items ? $list->items->count() : 0 }} 件商品
                            </p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-3">
                            <p class="text-[11px] text-gray-400">主要購買店家</p>
                            <p class="font-semibold text-gray-800 mt-0.5 truncate">
                                {{ $list->store_name ?? '未指定店家' }}
                            </p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

        <div x-show="subTab === 'posts'" x-transition>
            @if($completedPosts->isEmpty())
                <div class="rounded-2xl border border-dashed border-amber-200 bg-amber-50/30 p-12 text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white text-amber-400 shadow-sm">
                        <i class="bi bi-collection text-2xl"></i>
                    </div>
                    <p class="text-sm text-amber-700 font-semibold">尚未有已完成的代購貼文開團紀錄。</p>
                    <p class="mt-1 text-xs text-amber-500">當您自己發布的代購貼文結束或結案後，紀錄將會顯示於此。</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($completedPosts as $post)
                        <div class="p-4 bg-amber-50 rounded-xl border border-amber-100 space-y-3" x-data="{ showPostDetails: false }">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="font-bold text-gray-800">
                                        {{ $post->country ? '【'.$post->country.'】' : '' }}{{ $post->title ?? '未命名貼文' }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        代購期間：{{ optional($post->start_date)->format('Y/m/d') ?? '-' }} - {{ optional($post->end_date)->format('Y/m/d') ?? '-' }}
                                        ・ 完成日期：{{ $post->updated_at->format('Y-m-d') }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap justify-end">
                                    <span class="inline-flex text-[10px] font-bold px-2 py-0.5 rounded bg-purple-50 text-purple-700">已完成</span>
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-amber-600 bg-white border border-amber-200 rounded-lg hover:bg-amber-50 transition"
                                        @click="showPostDetails = !showPostDetails"
                                        x-text="showPostDetails ? '收合檢視' : '檢視貼文'">
                                    </button>
                                </div>
                            </div>

                            {{-- 展開檢視 --}}
                            <div x-show="showPostDetails" x-transition class="space-y-3">
                                <div class="rounded-lg border border-amber-100 bg-white/80 px-4 py-3">
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        {{ $post->description ?: '此貼文尚未填寫詳細說明。' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        地區：{{ $post->country ?? '-' }}{{ $post->city ? '・'.$post->city : '' }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    @forelse($post->products as $product)
                                        <div class="flex items-center gap-3 p-3 rounded-lg border border-amber-100 bg-white/80">
                                            <div class="w-14 h-14 rounded-lg bg-white border border-amber-100 overflow-hidden flex items-center justify-center text-amber-200 shrink-0">
                                                @if($product->display_image_url)
                                                    <img src="{{ $product->display_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <i class="bi bi-image text-xl"></i>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="font-bold text-gray-800 truncate">{{ $product->name }}</div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    單價：NT$ {{ number_format((float) $product->price, 0) }}
                                                    ・ 上限：{{ $product->max_quantity ?? '無限制' }}
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-xs text-gray-400 py-2">此貼文無商品資料。</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </section>
</div>

                    <!-- 分頁三：代購商品管理 -->
                    <div x-show="activeTab === 'product-management'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4">
                        <section id="product-management" class="bg-white rounded-2xl shadow-sm border border-blue-100 p-6">
                            <h3 class="text-lg font-bold text-blue-600 mb-6">代購團管理</h3>
                            @php
                                $managedProducts = \App\Models\PostProduct::query()
                                    ->whereHas('post', function ($query) {
                                        $query->where('user_id', Auth::id());
                                    })
                                    ->with('post:id,title,country,city,description,status,start_date,end_date')
                                    ->latest('id')
                                    ->get();
                                $managedPostGroups = $managedProducts
                                    ->groupBy('agent_post_id')
                                    ->sortByDesc(function ($products) {
                                        return optional($products->first()->post)->id ?? 0;
                                    });
                                $productFollowers = collect();
                                $productOrderedTotals = collect();
                                $managedPostIds = $managedPostGroups
                                    ->keys()
                                    ->filter(function ($id) {
                                        return ! empty($id);
                                    })
                                    ->map(function ($id) {
                                        return (int) $id;
                                    })
                                    ->values();

                                $shippedPostIds = collect();

                                if ($managedPostIds->isNotEmpty()) {
                                    $relatedOrders = \App\Models\Order::query()
                                        ->where('seller_id', Auth::id())
                                        ->where('source_type', \App\Models\AgentPost::class)
                                        ->whereIn('source_id', $managedPostIds)
                                        ->whereNotIn('status', ['cancelled', 'refunded'])
                                        ->with([
                                            'buyer:id,name',
                                            'items:id,order_id,product_id,quantity',
                                        ])
                                        ->latest('id')
                                        ->get();
                                    $shippedPostIds = $relatedOrders
                                        ->where('status', 'shipped')
                                        ->pluck('source_id')
                                        ->map(fn($id) => (int) $id)
                                        ->unique()
                                        ->values();

                                    $productFollowRows = $relatedOrders
                                        ->flatMap(function ($order) {
                                            return $order->items->map(function ($item) use ($order) {
                                                return [
                                                    'product_id' => (int) $item->product_id,
                                                    'buyer_id' => (int) $order->buyer_id,
                                                    'buyer_name' => optional($order->buyer)->name ?? '未知會員',
                                                    'quantity' => (int) $item->quantity,
                                                    'paid_at' => $order->paid_at,
                                                ];
                                            });
                                        })
                                        ->filter(function ($record) {
                                            return ! empty($record['product_id']);
                                        });

                                    $productOrderedTotals = $productFollowRows
                                        ->groupBy('product_id')
                                        ->map(function ($rows) {
                                            return (int) $rows->sum('quantity');
                                        });

                                    $productFollowers = $productFollowRows
                                        ->groupBy('product_id')
                                        ->map(function ($rows) {
                                            return $rows
                                                ->groupBy('buyer_id')
                                                ->map(function ($buyerRows) {
                                                    return [
                                                        'buyer_id'   => $buyerRows->first()['buyer_id'] ?? 0,
                                                        'buyer_name' => $buyerRows->first()['buyer_name'] ?? '未知會員',
                                                        'quantity' => (int) $buyerRows->sum('quantity'),
                                                        'paid_at' => $buyerRows->first()['paid_at'],
                                                    ];
                                                })
                                                ->values();
                                        });
                                }
                            $managedPostGroupsInProgress = $managedPostGroups
                                    ->filter(function ($products, $postId) use ($shippedPostIds) {
                                        $post = optional($products->first())->post;
                                        $status = optional($post)->status;
                                        return ! $shippedPostIds->contains((int) $postId)
                                            && $status !== 'shipped'
                                            && $status !== 'arrivaled'
                                            && $status !== 'completed';
                                    });
                                $managedPostGroupsShipped = $managedPostGroups
                                    ->filter(function ($products, $postId) use ($shippedPostIds) {
                                        $post = optional($products->first())->post;
                                        $status = optional($post)->status;
                                        return ($shippedPostIds->contains((int) $postId) || $status === 'shipped' || $status === 'arrivaled')
                                            && $status !== 'completed';
                                    });
                            @endphp

                            <div x-data="{ productTab: 'in-progress' }">
                                <div class="mb-5 flex flex-wrap items-center gap-3">
                                    <button type="button" @click="productTab = 'in-progress'"
                                        :class="productTab === 'in-progress' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                        class="inline-flex items-center rounded-full border px-5 py-2 text-sm font-bold transition">進行中 ({{ $managedPostGroupsInProgress->count() }})</button>
                                    <button type="button" @click="productTab = 'shipped'"
                                        :class="productTab === 'shipped' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                                        class="inline-flex items-center rounded-full border px-5 py-2 text-sm font-bold transition">已出貨 ({{ $managedPostGroupsShipped->count() }})</button>
                                </div>

                            <div class="space-y-4" x-show="productTab === 'in-progress'">
                                @forelse($managedPostGroupsInProgress as $products)
                                    @php
                                        $post = optional($products->first())->post;
                                        $postStatus = optional($post)->status;
                                        $statusLabel = match($postStatus) {
                                            'draft'     => '編輯中',
                                            'open'      => '進行中',
                                            'closed'    => '已截單',
                                            'shipped'   => '已出貨',
                                            'arrivaled' => '已到貨',
                                            'completed' => '已完成',
                                            default     => $postStatus ?? '未知',
                                        };
                                        $statusClasses = match($postStatus) {
                                            'draft'     => 'bg-amber-50 text-amber-600',
                                            'open'      => 'bg-emerald-50 text-emerald-600',
                                            'shipped'   => 'bg-blue-50 text-blue-600',
                                            'arrivaled' => 'bg-indigo-50 text-indigo-600',
                                            'completed' => 'bg-gray-100 text-gray-500',
                                            default     => 'bg-emerald-50 text-emerald-600',
                                        };
                                    @endphp
                                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 space-y-3" x-data="{ showPostDetails: false }">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <div class="font-bold text-gray-800">
                                                    {{ optional($post)->country ? '【'.optional($post)->country.'】' : '' }}{{ optional($post)->title ?? '未命名貼文' }}
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    代購期間：{{ optional(optional($post)->start_date)->format('Y/m/d') ?? '-' }} - {{ optional(optional($post)->end_date)->format('Y/m/d') ?? '-' }}
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 flex-wrap justify-end">
                                                <span class="inline-flex text-[10px] font-bold px-2 py-0.5 rounded {{ $statusClasses }}">{{ $statusLabel }}</span>
                                                <button type="button"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-600 bg-white border border-blue-200 rounded-lg hover:bg-blue-100 transition"
                                                    @click="showPostDetails = !showPostDetails"
                                                    x-text="showPostDetails ? '收合檢視' : '檢視貼文'">
                                                </button>
                                                <button type="button"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-amber-600 bg-white border border-amber-200 rounded-lg hover:bg-amber-50 transition"
                                                    onclick="document.getElementById('manage-modal-{{ $post->id }}').classList.remove('hidden'); document.getElementById('manage-modal-{{ $post->id }}').classList.add('flex');">
                                                    管理
                                                </button>
                                                @if($post->status === 'open')
                                                <form method="POST" action="{{ route('agent.posts.ship', $post->id) }}" class="inline" onsubmit="return confirm('確定要將此貼文標記為已出貨？')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-emerald-500 border border-emerald-500 rounded-lg hover:bg-emerald-600 transition">
                                                        出貨
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </div>

                                        <div x-show="showPostDetails" x-transition class="space-y-3">
                                            <div class="rounded-lg border border-blue-100 bg-white/80 px-4 py-3">
                                                <p class="text-sm text-gray-700 leading-relaxed">
                                                    {{ optional($post)->description ?: '此貼文尚未填寫詳細說明。' }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-2">
                                                    地區：{{ optional($post)->country ?? '-' }}{{ optional($post)->city ? '・'.optional($post)->city : '' }}
                                                </p>
                                            </div>

                                            <div class="space-y-2">
                                                @foreach($products as $product)
                                                    <div class="flex items-center gap-3 p-3 rounded-lg border border-blue-100 bg-white/80">
                                                        <div class="w-14 h-14 rounded-lg bg-white border border-blue-100 overflow-hidden flex items-center justify-center text-blue-200 shrink-0">
                                                            @if($product->display_image_url)
                                                                <img src="{{ $product->display_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                                            @else
                                                                <i class="bi bi-image text-xl"></i>
                                                            @endif
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <div class="font-bold text-gray-800 truncate">{{ $product->name }}</div>
                                                            @php
                                                                $orderedTotal = (int) $productOrderedTotals->get((int) $product->id, 0);
                                                                $remainingQty = is_null($product->max_quantity)
                                                                    ? '無限制'
                                                                    : max(0, (int) $product->max_quantity - $orderedTotal);
                                                                $followers = $productFollowers->get((int) $product->id, collect());
                                                                $totalOrdered = $followers->sum(function($f) { return $f['quantity']; });
                                                            @endphp
                                                            <div class="text-xs text-gray-500 mt-1">
                                                                單價：NT$ {{ number_format((float) $product->price, 0) }}
                                                                ・ 上限：{{ $product->max_quantity ?? '無限制' }}
                                                                ・ 目前跟單總數：<span class="text-indigo-600 font-bold">{{ $totalOrdered }}</span>
                                                                ・ 剩餘可跟單：{{ $remainingQty }}
                                                            </div>
                                                            <div class="mt-2 text-xs text-gray-600">
                                                                <div class="font-semibold text-gray-700 mb-1">
                                                                    跟單紀錄：
                                                                    @if($followers->count() > 0)
                                                                        <span class="ml-1 text-indigo-600 font-bold">共 {{ $followers->count() }} 人</span>
                                                                    @endif
                                                                </div>
                                                                @forelse($followers as $follower)
                                                                    <div class="mb-1 flex items-center gap-2">
                                                                        <span>{{ $follower['buyer_name'] }}：{{ $follower['quantity'] }} 件</span>
                                                                        @if(!empty($follower['paid_at']))
                                                                            <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-[10px] font-bold">已付款</span>
                                                                        @else
                                                                            <span class="inline-flex items-center rounded-full bg-red-50 text-red-500 px-2 py-0.5 text-[10px] font-bold">未付款</span>
                                                                        @endif
                                                                    </div>
                                                                @empty
                                                                    <div class="text-gray-400">目前尚無人跟團</div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 管理 Modal --}}
                                    <div id="manage-modal-{{ $post->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
                                         onclick="if(event.target===this){this.classList.add('hidden');this.classList.remove('flex');}">
                                        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl max-h-[88vh] overflow-y-auto">
                                            <div class="flex items-center justify-between border-b px-6 py-4">
                                                <h4 class="text-lg font-bold text-gray-800">管理跟單 - {{ $post->title }}</h4>
                                                <button type="button" class="text-2xl text-gray-400 hover:text-gray-600"
                                                    onclick="document.getElementById('manage-modal-{{ $post->id }}').classList.add('hidden');document.getElementById('manage-modal-{{ $post->id }}').classList.remove('flex');">&times;</button>
                                            </div>
                                            <div class="px-6 py-4 space-y-4">
                                                @foreach($managedPostGroups->get($post->id ?? 0, collect()) as $product)
                                                    @php $mFollowers = $productFollowers->get((int) $product->id, collect()); @endphp
                                                    @if($mFollowers->count() > 0)
                                                        <div class="rounded-xl border border-gray-100 p-4">
                                                            <p class="font-bold text-gray-800 mb-3 text-sm">{{ $product->name }}</p>
                                                            <div class="space-y-2">
                                                                @foreach($mFollowers as $follower)
                                                                    @php
                                                                        $followerOrder = \App\Models\Order::where('seller_id', Auth::id())
                                                                            ->where('source_id', $post->id)
                                                                            ->where('buyer_id', $follower['buyer_id'] ?? 0)
                                                                            ->where('status', 'pending_payment')
                                                                            ->first();
                                                                    @endphp
                                                                    <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2">
                                                                        <div class="flex items-center gap-3">
                                                                            <span class="text-sm text-gray-800">{{ $follower['buyer_name'] }}</span>
                                                                            <span class="text-xs text-gray-500">{{ $follower['quantity'] }} 件</span>
                                                                            @if(!empty($follower['paid_at']))
                                                                                <span class="text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5">已付款</span>
                                                                            @else
                                                                                <span class="text-[10px] font-bold rounded-full bg-red-50 text-red-500 px-2 py-0.5">未付款</span>
                                                                            @endif
                                                                        </div>
                                                                        @if($followerOrder && empty($follower['paid_at']))
                                                                            <form method="POST" action="{{ route('agent.orders.cancel', $followerOrder->id) }}"
                                                                                  onsubmit="return confirm('確定要取消 {{ $follower['buyer_name'] }} 的訂單？數量將會回補。')">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-semibold">取消訂單</button>
                                                                            </form>
                                                                        @elseif(!empty($follower['paid_at']))
                                                                            <span class="text-xs text-gray-400">已付款不可取消</span>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                                @if($managedPostGroups->get($post->id ?? 0, collect())->every(fn($p) => $productFollowers->get((int)$p->id, collect())->count() === 0))
                                                    <p class="text-gray-400 text-sm text-center py-6">目前尚無人跟團。</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-gray-400 text-sm text-center py-8 border border-dashed border-blue-200 rounded-xl">
                                        目前沒有進行中的代購貼文商品管理資料。
                                    </div>
                                @endforelse
                            </div>

                            <div class="space-y-4" x-show="productTab === 'shipped'" x-cloak>
                                @forelse($managedPostGroupsShipped as $products)
                                    @php
                                        $post = optional($products->first())->post;
                                        $postStatus = optional($post)->status;
                                        $statusLabel = match($postStatus) {
                                            'draft'     => '編輯中',
                                            'open'      => '進行中',
                                            'closed'    => '已截單',
                                            'shipped'   => '已出貨',
                                            'arrivaled' => '已到貨',
                                            'completed' => '已完成',
                                            default     => $postStatus ?? '未知',
                                        };
                                        $statusClasses = match($postStatus) {
                                            'draft'     => 'bg-amber-50 text-amber-600',
                                            'open'      => 'bg-emerald-50 text-emerald-600',
                                            'shipped'   => 'bg-blue-50 text-blue-600',
                                            'arrivaled' => 'bg-indigo-50 text-indigo-600',
                                            'completed' => 'bg-gray-100 text-gray-500',
                                            default     => 'bg-emerald-50 text-emerald-600',
                                        };
                                    @endphp
                                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 space-y-3" x-data="{ showPostDetails: false }">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <div class="font-bold text-gray-800">
                                                    {{ optional($post)->country ? '【'.optional($post)->country.'】' : '' }}{{ optional($post)->title ?? '未命名貼文' }}
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    代購期間：{{ optional(optional($post)->start_date)->format('Y/m/d') ?? '-' }} - {{ optional(optional($post)->end_date)->format('Y/m/d') ?? '-' }}
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 flex-wrap justify-end">
                                                <span class="inline-flex text-[10px] font-bold px-2 py-0.5 rounded {{ $statusClasses }}">{{ $statusLabel }}</span>
                                                <button type="button"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-600 bg-white border border-blue-200 rounded-lg hover:bg-blue-100 transition"
                                                    @click="showPostDetails = !showPostDetails"
                                                    x-text="showPostDetails ? '收合檢視' : '檢視貼文'">
                                                </button>
                                                @if($postStatus === 'arrivaled')
                                                    <button type="button" disabled
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                                                        到貨
                                                    </button>
                                                @else
                                                    <form method="POST" action="{{ route('agent.posts.arrive', $post->id) }}" class="inline" onsubmit="return confirm('確定標記為已到貨？買家將收到到貨通知。')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-indigo-500 border border-indigo-500 rounded-lg hover:bg-indigo-600 transition">
                                                            到貨
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('agent.posts.complete', $post->id) }}" class="inline" onsubmit="return confirm('確定完成此代購貼文？完成後將移至歷史紀錄。')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-emerald-500 border border-emerald-500 rounded-lg hover:bg-emerald-600 transition">
                                                        完成
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        {{-- 展開內容 --}}
                                        <div x-show="showPostDetails" x-transition class="space-y-3">
                                            <div class="rounded-lg border border-blue-100 bg-white/80 px-4 py-3">
                                                <p class="text-sm text-gray-700 leading-relaxed">
                                                    {{ optional($post)->description ?: '此貼文尚未填寫詳細說明。' }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-2">
                                                    地區：{{ optional($post)->country ?? '-' }}{{ optional($post)->city ? '・'.optional($post)->city : '' }}
                                                </p>
                                            </div>
                                            <div class="space-y-2">
                                                @foreach($products as $product)
                                                    <div class="flex items-center gap-3 p-3 rounded-lg border border-blue-100 bg-white/80">
                                                        <div class="w-14 h-14 rounded-lg bg-white border border-blue-100 overflow-hidden flex items-center justify-center text-blue-200 shrink-0">
                                                            @if($product->display_image_url)
                                                                <img src="{{ $product->display_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                                            @else
                                                                <i class="bi bi-image text-xl"></i>
                                                            @endif
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <div class="font-bold text-gray-800 truncate">{{ $product->name }}</div>
                                                            @php
                                                                $orderedTotal = (int) $productOrderedTotals->get((int) $product->id, 0);
                                                                $followers    = $productFollowers->get((int) $product->id, collect());
                                                                $totalOrdered = $followers->sum(function($f) { return $f['quantity']; });
                                                            @endphp
                                                            <div class="text-xs text-gray-500 mt-1">
                                                                單價：NT$ {{ number_format((float) $product->price, 0) }}
                                                                ・ 上限：{{ $product->max_quantity ?? '無限制' }}
                                                                ・ 跟單總數：<span class="text-indigo-600 font-bold">{{ $totalOrdered }}</span>
                                                            </div>
                                                            <div class="mt-2 text-xs text-gray-600">
                                                                @forelse($followers as $follower)
                                                                    <div class="flex items-center gap-2 py-0.5">
                                                                        <span>{{ $follower['buyer_name'] }}</span>
                                                                        <span class="text-gray-400">{{ $follower['quantity'] }} 件</span>
                                                                        @if(!empty($follower['paid_at']))
                                                                            <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 px-2 py-0.5 text-[10px] font-bold">已付款</span>
                                                                        @else
                                                                            <span class="inline-flex items-center rounded-full bg-red-50 text-red-500 px-2 py-0.5 text-[10px] font-bold">未付款</span>
                                                                        @endif
                                                                    </div>
                                                                @empty
                                                                    <div class="text-gray-400">目前尚無人跟團</div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-gray-400 text-sm text-center py-8 border border-dashed border-blue-200 rounded-xl">
                                        目前沒有已出貨的代購貼文商品管理資料。
                                    </div>
                                @endforelse
                            </div>
                            </div>
                        </section>
                    </div>

                    <!-- 分頁三：我的收藏請購清單 (覆蓋顯示) -->
                    <div x-show="activeTab === 'favorites'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4">
                        <section id="favorites" class="bg-white rounded-2xl shadow-sm border border-pink-100 p-6">
                            <h3 class="text-lg font-bold text-pink-600 mb-6">我的收藏請購清單</h3>
                            <div class="space-y-4" id="favorite-list-block">
                                @php
                                    $favoriteRequestLists = Auth::user()->favorites
                                        ? Auth::user()->favorites
                                            ->where('favoriteable_type', 'App\\Models\\RequestList')
                                            ->load('favoriteable')
                                            ->pluck('favoriteable')
                                            ->filter()
                                        : collect([]);
                                @endphp
                                @forelse($favoriteRequestLists as $favList)
                                    <div class="favorite-list-item flex items-center gap-4 p-4 bg-pink-50 rounded-xl border border-pink-100" data-request-list-id="{{ $favList->id }}">
                                        <button type="button"
                                            class="favorite-remove-btn w-10 h-10 rounded-full bg-white text-pink-500 flex items-center justify-center shadow-sm border border-pink-100 transition hover:bg-pink-100"
                                            data-request-list-id="{{ $favList->id }}"
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
                        </section>
                    </div>


                     <!-- 分頁三：最近撥款紀錄 -->
                    <div x-show="activeTab === 'payouts'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4">
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

        <!-- 移除確認彈窗 (Modal) -->
        <div id="favorite-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
            <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl">
                <div class="w-16 h-16 bg-pink-50 text-pink-500 rounded-full flex items-center justify-center mx-auto text-2xl mb-4">
                    <i class="bi bi-heartbreak"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-800 text-center mb-2">確定取消收藏？</h4>
                <p class="text-gray-500 text-sm text-center mb-6">取消後，此清單將從您的收藏夾中移除。</p>
                <div class="flex gap-3">
                    <button id="favorite-modal-cancel" class="flex-1 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition">取消</button>
                    <button id="favorite-modal-confirm" class="flex-1 py-3 bg-pink-600 text-white rounded-xl font-bold hover:bg-pink-700 transition shadow-lg shadow-pink-100">確定移除</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let pendingRemoveId = null;
            const modal = document.getElementById('favorite-modal');
            const confirmBtn = document.getElementById('favorite-modal-confirm');
            const cancelBtn = document.getElementById('favorite-modal-cancel');

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.favorite-remove-btn');
                if (btn) {
                    pendingRemoveId = btn.getAttribute('data-request-list-id');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            });

            cancelBtn.addEventListener('click', function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                pendingRemoveId = null;
            });

            confirmBtn.addEventListener('click', function() {
                if (!pendingRemoveId) return;
                fetch("{{ route('favorite.toggle') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ type: 'request_list', id: pendingRemoveId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'removed') {
                        const item = document.querySelector('.favorite-list-item[data-request-list-id="' + pendingRemoveId + '"]');
                        if (item) item.remove();
                    }
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    pendingRemoveId = null;
                });
            });
        });
    </script>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        function openAgentRequestChatModal(id) {
            const modal = document.getElementById(`agent-request-chat-modal-${id}`);
            if (!modal) {
                return;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');

            const messagesBox = document.getElementById(`agent-request-chat-messages-${id}`);
            if (messagesBox) {
                messagesBox.scrollTop = messagesBox.scrollHeight;
            }

            // 點進聊天室才標記已讀，並廣播給對方
            fetch(`/request-list/${id}/chat/read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': AGENT_CSRF, 'Accept': 'application/json' }
            }).catch(() => {});

            // 清除這個聊天室按鈕的未讀紅點
            clearAgentChatBadge(id);

            // 把聊天室內現有的「未讀」標示全部更新成「已讀」
            if (messagesBox) {
                messagesBox.querySelectorAll('.agent-msg-read-status').forEach(el => {
                    el.style.color = '#6366f1';
                    el.textContent = '已讀';
                });
            }
        }

        function closeAgentRequestChatModal(id) {
            const modal = document.getElementById(`agent-request-chat-modal-${id}`);
            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function handleAgentRequestChatBackdrop(event, id) {
            if (event.target.id === `agent-request-chat-modal-${id}`) {
                closeAgentRequestChatModal(id);
            }
        }

        function appendAgentRequestChatMessage(requestListId, message) {
            const messagesBox = document.getElementById(`agent-request-chat-messages-${requestListId}`);
            if (!messagesBox || !message) {
                return;
            }

            const row = document.createElement('div');
            row.className = 'mb-3 flex justify-end';
            row.innerHTML = `
                <div class="max-w-[75%]">
                    <div class="rounded-xl border px-3 py-2 bg-indigo-100 border-indigo-200">
                        <p class="text-xs text-gray-500">${message.name ?? message.sender_name ?? ''}</p>
                        <p class="mt-1 text-sm text-gray-800 break-words"></p>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 text-right">${message.time ?? message.created_at ?? ''}</p>
                </div>
            `;

            const bodyNode = row.querySelector('.break-words');
            if (bodyNode) {
                bodyNode.textContent = message.text ?? message.body ?? '';
            }

            const emptyState = messagesBox.querySelector('.agent-request-chat-empty');
            if (emptyState) {
                emptyState.remove();
            }

            messagesBox.appendChild(row);
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }

        document.addEventListener('submit', async (event) => {
            const form = event.target.closest('.agent-request-chat-form');
            if (!form) {
                return;
            }

            event.preventDefault();

            const input = form.querySelector('input[name="body"]');
            const submitButton = form.querySelector('button[type="submit"]');
            const requestListId = form.dataset.requestListId;
            const messageText = (input?.value || '').trim();

            if (!submitButton || !messageText) {
                return;
            }

            submitButton.disabled = true;
            const originalText = submitButton.textContent;
            submitButton.textContent = '送出中...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(payload?.message || '訊息送出失敗');
                }

                appendAgentRequestChatMessage(requestListId, payload);
                input.value = '';
                input.focus();
            } catch (error) {
                alert(error.message || '訊息送出失敗，請稍後再試。');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = originalText || '送出';
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            document.querySelectorAll('.agent-request-chat-modal').forEach((modal) => {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.classList.remove('overflow-hidden');
                }
            });
        });

        // ── Pusher 即時聊天（代購人請託單聊天室）────────────────
        const AGENT_MY_ID = {{ Auth::id() }};
        const AGENT_CSRF  = document.querySelector('meta[name=csrf-token]').getAttribute('content');

        // 初始化未讀數（頁面載入時顯示既有未讀）
        @php
            $agentInitUnread = \App\Models\Message::where('receiver_id', Auth::id())
                ->whereNotNull('request_list_id')
                ->whereNull('read_at')
                ->selectRaw('request_list_id, count(*) as cnt')
                ->groupBy('request_list_id')
                ->pluck('cnt', 'request_list_id');
        @endphp
        const agentInitUnreadData = @json($agentInitUnread);

        const pusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
            cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
            forceTLS: true,
            authEndpoint: '/broadcasting/auth',
            auth: { headers: { 'X-CSRF-TOKEN': AGENT_CSRF } }
        });

        const myChannel = pusher.subscribe('private-chat.' + AGENT_MY_ID);

        // 未讀徽章管理
        const agentUnreadCounts = {};

        // 頁面載入時初始化既有未讀紅點
        Object.entries(agentInitUnreadData).forEach(([id, count]) => {
            setTimeout(() => setAgentChatBadge(parseInt(id), count), 100);
        });

        function setAgentChatBadge(requestListId, count) {
            agentUnreadCounts[requestListId] = count;
            // 更新所有對應這個 requestList 的聊天按鈕
            document.querySelectorAll(`.agent-chat-badge[data-request-list-id="${requestListId}"]`).forEach(badge => {
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            });
        }

        function clearAgentChatBadge(requestListId) {
            setAgentChatBadge(requestListId, 0);
        }

        // 收到新訊息
        myChannel.bind('message.sent', function (data) {
            if (!data.requestListId) return;
            // 自己發的訊息已由 sendAgentRequestChat() 即時追加，不再重複處理
            if (data.senderId === AGENT_MY_ID) return;
            const box = document.getElementById(`agent-request-chat-messages-${data.requestListId}`);
            if (!box) return;

            const emptyTip = document.getElementById(`agent-empty-tip-${data.requestListId}`);
            if (emptyTip) emptyTip.remove();

            const row = document.createElement('div');
            row.className = 'mb-3 flex justify-start';
            row.innerHTML = `
                <div class="max-w-[75%]">
                    <div class="rounded-xl border px-3 py-2 bg-white border-gray-200">
                        <p class="text-xs text-gray-500">${data.userName}</p>
                        <p class="mt-1 text-sm text-gray-800 break-words"></p>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 flex items-center gap-1 justify-start">${data.time}</p>
                </div>`;
            row.querySelector('.break-words').textContent = data.messageContent;
            box.appendChild(row);
            box.scrollTop = box.scrollHeight;

            // 若聊天室是開著的，立即標記已讀並清除紅點
            const modal = document.getElementById(`agent-request-chat-modal-${data.requestListId}`);
            if (modal && !modal.classList.contains('hidden')) {
                fetch(`/request-list/${data.requestListId}/chat/read`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': AGENT_CSRF, 'Accept': 'application/json' }
                }).catch(() => {});
                clearAgentChatBadge(data.requestListId);
            } else {
                // 聊天室是關閉的，顯示未讀紅點
                const current = agentUnreadCounts[data.requestListId] || 0;
                setAgentChatBadge(data.requestListId, current + 1);
            }
        });

        // 對方已讀 → 更新最新訊息的已讀狀態
        myChannel.bind('message.read', function (data) {
            if (!data.requestListId) return;
            const box = document.getElementById(`agent-request-chat-messages-${data.requestListId}`);
            if (!box) return;
            box.querySelectorAll('.agent-msg-read-status').forEach(el => {
                el.style.color = '#6366f1';
                el.textContent = '已讀';
            });
        });

        // ── fetch 即時送出 ────────────────────────────────────
        function sendAgentRequestChat(input, btn) {
            const text = input.value.trim();
            if (!text) return;
            const requestListId = input.dataset.requestListId;
            const receiverId    = input.dataset.receiverId;
            const sendUrl       = input.dataset.sendUrl;
            input.value = '';
            btn.disabled = true;

            // 帶 X-Socket-ID 讓 toOthers() 排除自己，避免 Pusher 廣播重複
            const socketId = (typeof pusher !== 'undefined' && pusher?.connection?.socket_id) ? pusher.connection.socket_id : '';

            fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': AGENT_CSRF,
                    'Accept': 'application/json',
                    'X-Socket-ID': socketId,
                },
                body: JSON.stringify({ body: text, receiver_id: parseInt(receiverId) }),
            })
            .then(r => {
                if (!r.ok) { btn.disabled = false; return Promise.reject('send_failed'); }
                return r.json();
            })
            .then(msg => {
                const name = msg.name ?? msg.sender_name ?? msg.userName ?? '';
                const text = msg.text ?? msg.body ?? msg.message ?? '';
                const time = msg.time ?? msg.created_at ?? '';

                const box = document.getElementById(`agent-request-chat-messages-${requestListId}`);
                if (!box) return;
                const emptyTip = document.getElementById(`agent-empty-tip-${requestListId}`);
                if (emptyTip) emptyTip.remove();
                box.querySelectorAll('.agent-msg-read-status').forEach(el => el.remove());
                const row = document.createElement('div');
                row.className = 'mb-3 flex justify-end';
                row.innerHTML = `
                    <div class="max-w-[75%]">
                        <div class="rounded-xl border px-3 py-2 bg-indigo-100 border-indigo-200">
                            <p class="text-xs text-gray-500"></p>
                            <p class="mt-1 text-sm text-gray-800 break-words"></p>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 flex items-center gap-1 justify-end">
                            <span></span>
                            <span class="agent-msg-read-status" style="color:#94a3b8">未讀</span>
                        </p>
                    </div>`;
                row.querySelectorAll('p')[0].textContent = name;
                row.querySelectorAll('p')[1].textContent = text;
                row.querySelectorAll('span')[0].textContent = time;
                box.appendChild(row);
                box.scrollTop = box.scrollHeight;
                btn.disabled = false;
                input.focus();
            })
            .catch(() => { btn.disabled = false; });
        }

        document.querySelectorAll('.agent-request-chat-send-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const input = document.querySelector(
                    `.agent-request-chat-input[data-request-list-id="${this.dataset.requestListId}"]`
                );
                if (input) sendAgentRequestChat(input, this);
            });
        });

        document.querySelectorAll('.agent-request-chat-input').forEach(input => {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    const btn = document.querySelector(
                        `.agent-request-chat-send-btn[data-request-list-id="${this.dataset.requestListId}"]`
                    );
                    if (btn) sendAgentRequestChat(this, btn);
                }
            });
        });

        // modal 開啟時捲到底
        document.querySelectorAll('.agent-request-chat-modal').forEach(modal => {
            const observer = new MutationObserver(() => {
                modal.querySelectorAll('[id^="agent-request-chat-messages-"]').forEach(box => {
                    box.scrollTop = box.scrollHeight;
                });
            });
            observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
        });

    </script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        // 尋找點擊目標是不是開啟代購聊天 Modal 的按鈕
        // 🚨 如果你開啟 Modal 的按鈕 class 不是 .agent-request-chat-btn，請換成你的按鈕 class
        const btn = e.target.closest('.agent-request-chat-btn') || e.target.closest('.agent-request-chat-send-btn');
        if (btn) {
            const listId = btn.dataset.requestListId;
            
            // 尋找代購單列表旁的未讀紅點並立刻隱藏
            const badge = document.querySelector(`.unread-badge-buyer[data-request-list-id="${listId}"]`);
            if (badge) {
                badge.style.display = 'none';
            }
        }
    });
});
</script>
</x-app-layout>