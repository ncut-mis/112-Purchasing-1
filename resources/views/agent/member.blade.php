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

    <!-- 使用 Alpine.js 控制分頁，預設顯示 'posts' -->
    <div x-data="{ activeTab: 'posts' }" class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- 數據統計區 (始終顯示) -->
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
                                <span>我的代購貼文</span>
                            </a>
                            <!-- 2. 訂單管理 -->
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <i class="bi bi-file-earmark-medical text-indigo-500 text-lg"></i>
                                <span>訂單管理</span>
                            </a>
                            <!-- 3. 代購商品管理 -->
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <i class="bi bi-box-seam text-blue-500 text-lg"></i>
                                <span>代購商品管理</span>
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
                            <!-- 6. 收藏請購清單 -->
                            <a href="#" @click.prevent="activeTab = 'favorites'" :class="activeTab === 'favorites' ? 'bg-pink-50 text-pink-600 font-bold' : 'text-gray-600'" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition">
                                <i class="bi bi-heart text-pink-500 text-lg"></i>
                                <span>收藏請購清單</span>
                            </a>
                            <!-- 7. 評價中心 -->
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <i class="bi bi-star text-yellow-500 text-lg"></i>
                                <span>評價中心</span>
                            </a>
                            <!-- 8. 物流設定 -->
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <i class="bi bi-truck text-indigo-500 text-lg"></i>
                                <span>物流設定</span>
                            </a>

                            <div class="border-t border-gray-50 my-2 pt-2"></div>
                            
                            <!-- 9. 發布代購貼文 (置底強調) -->
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-indigo-600 font-bold hover:bg-indigo-50 transition">
                                <i class="bi bi-plus-circle text-lg"></i>
                                <span>發布代購貼文</span>
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

                <!-- 右側主內容區 -->
                <div class="w-full lg:w-3/4 space-y-8">

                    
                    <!-- 分頁一：我的代購貼文 (預設顯示) -->
                    <div x-show="activeTab === 'posts'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4">
                        

                      <!-- 我的代購連線 -->
                      <section id="connections" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        @php
                            $myAgentPosts = \App\Models\AgentPost::with(['products'])->withCount('products')
                                ->where('user_id', Auth::id())
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
                                        <h6 class="font-bold text-gray-800 text-sm truncate">​:codex-terminal-citation[codex-terminal-citation]{line_range_start=1 line_range_end=866 terminal_chunk_id={{ $post->country }}】{{ $post->title }}</h6>
                                        <p class="text-[10px] text-gray-400">銷售期間: {{ optional($post->start_date)->format('Y-m-d') }} ~ {{ optional($post->end_date)->format('Y-m-d') }}</p>
                                        <div class="mt-2 flex gap-2 flex-wrap">
                                            <span class="text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded">{{ $post->products_count }} 項商品</span>
                                            <span class="text-[10px] text-green-600 font-bold bg-green-50 px-2 py-0.5 rounded">{{ $post->status === 'open' ? '進行中' : $post->status }}</span>
                                        </div>
                                        <div class="mt-3 flex gap-2">
                                            <button type="button" class="agent-post-view-btn text-[11px] px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition" data-modal-id="agent-post-view-modal-{{ $post->id }}">檢視</button>
                                            <button type="button" class="agent-post-edit-btn text-[11px] px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition" data-modal-id="agent-post-edit-modal-{{ $post->id }}">編輯</button>
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
                                                        <input type="hidden" name="products[{{ $pIndex }}][existing_image]" value="{{ $product->image_path }}">
                                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                            <input type="text" name="products[{{ $pIndex }}][name]" value="{{ $product->name }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="商品名稱" required>
                                                            <input type="number" min="0" step="0.01" name="products[{{ $pIndex }}][price]" value="{{ $product->price }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="單價" required>
                                                            <input type="number" min="1" step="1" name="products[{{ $pIndex }}][max_quantity]" value="{{ $product->max_quantity }}" class="w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="最高數量" required>
                                                        </div>
                                                        <div class="mt-3">
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

                                addBtn.addEventListener('click', function () {
                                    const count = container.querySelectorAll('.edit-product-item').length;
                                    if (count >= maxItems) return;
                                    const html = template.replaceAll('__INDEX__', String(nextIndex)).replaceAll('__NUMBER__', String(count + 1));
                                    container.insertAdjacentHTML('beforeend', html);
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
                            });
                        });
                     </script>
                     
                     


                    </div>

                    <!-- 分頁二：我的收藏請購清單 (覆蓋顯示) -->
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
</x-app-layout>