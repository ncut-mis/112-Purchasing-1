                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">目前收藏內容</h3>
                                </div>

                                <div class="flex items-center gap-4">                        

                                <!-- 搜尋框 -->

                                <form method="GET" action="{{ route('dashboard') }}" class="relative w-full md:w-80">
    <input type="hidden" name="section" value="favorite-posts">

    <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#ec4899] transition">
        <i class="bi bi-search"></i>
        </button>

    <input 
        type="search" 
        name="favorite_search" 
        placeholder="搜尋貼文標題、代購人..." 
        value="{{ request('favorite_search') }}"
        class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#ec4899] focus:border-[#ec4899] shadow-sm transition outline-none"
    >
</form>
                            </div>
                            </div>

                            @if(request('favorite_search'))
                                <div class="mb-4 rounded-lg border border-pink-200 bg-pink-50 px-4 py-3 text-sm text-pink-700">
                                    搜尋「{{ request('favorite_search') }}」找到 {{ $favoriteAgentPosts->total() }} 筆收藏貼文。
                                    <a href="{{ route('dashboard', ['section' => 'favorite-posts']) }}" class="ml-2 font-semibold hover:underline">清除搜尋</a>
                                </div>
                            @endif

                            <div class="space-y-2.5" id="favorite-post-list">
    @forelse($favoriteAgentPosts as $favoriteAgentPost)
        <article class="favorite-post-item flex flex-col gap-2.5 rounded-[20px] border border-pink-100 bg-[#fff8fc] px-3.5 py-3 shadow-sm lg:flex-row lg:items-center" data-agent-post-id="{{ $favoriteAgentPost->id }}">
@php
                    $post = $favoriteAgentPost->favoriteable;
                    $firstProduct = optional($post?->products)->first();
                    $image = $firstProduct?->display_image_url ?? $post?->cover_image_url;
                @endphp

                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-[18px] bg-white shadow-sm">
                    @if($image)
                        <img src="{{ $image }}" alt="{{ $post->title ?? '' }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-white text-xl text-pink-300">♡</div>
                    @endif
                </div>
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <h4 class="truncate text-[0.9rem] font-semibold text-slate-700">{{ $favoriteAgentPost->title }}</h4>
                        <p class="mt-1 text-[0.82rem] text-slate-600">代購人：{{ optional($favoriteAgentPost->user)->name ?? '匿名代購人' }}</p>
                        <div class="mt-1 flex flex-wrap items-center gap-x-5 gap-y-1 text-[0.82rem] text-slate-500">
                            <span>貼文建立：{{ optional($favoriteAgentPost->created_at)->format('Y-m-d') }}</span>
                            <span>可代購商品：{{ $favoriteAgentPost->products->count() }} 項</span>
                            <span>狀態：{{ $favoriteAgentPost->status === 'open' ? '接單中' : $favoriteAgentPost->status }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 lg:justify-end">
                        <button type="button" 
                          onclick="openFollowChoiceModal('{{ $favoriteAgentPost->id }}', '{{ $favoriteAgentPost->title }}', '{{ route('agent.posts.search', ['search' => $favoriteAgentPost->title]) }}')"
                          class="shrink-0 text-[0.9rem] font-semibold text-pink-500 transition hover:text-pink-600 hover:underline">
                          在此跟單或至首頁跟單
                        </button>

                        <button
                            type="button"
                            class="dashboard-favorite-toggle inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-pink-100 bg-white text-pink-500 shadow-sm transition hover:bg-pink-50"
                            data-agent-post-id="{{ $favoriteAgentPost->id }}"
                            aria-label="取消收藏貼文"
                            aria-pressed="true"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                                <path d="M12.001 4.529c2.349-2.532 6.15-2.533 8.498-.001 2.41 2.6 2.41 6.815 0 9.416l-7.66 8.266a1.14 1.14 0 0 1-1.677 0l-7.66-8.266c-2.41-2.601-2.41-6.817 0-9.416 2.348-2.532 6.149-2.531 8.499.001Z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </article>
    @empty
        <div class="rounded-2xl border border-dashed border-pink-200 bg-pink-50/40 px-6 py-12 text-center text-sm text-gray-500">
            @if(request('favorite_search'))
                找不到符合「{{ request('favorite_search') }}」的收藏貼文。
            @else
                目前尚未收藏任何內容，請先到首頁的「代購團」按下愛心收藏。
            @endif
        </div>
    @endforelse
</div>

                            @if($favoriteAgentPosts->hasPages())
                                <div class="mt-6">
                                    {{ $favoriteAgentPosts->appends(['section' => 'favorite-posts', 'favorite_search' => request('favorite_search')])->links() }}
                                </div>
                            @endif
                        </div>

                        <div id="favorite-unfavorite-modal" class="hidden fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/45 px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="favorite-unfavorite-modal-title">
                            <div class="w-full max-w-[400px] rounded-[2rem] bg-white px-5 py-5 text-center shadow-[0_24px_80px_rgba(15,23,42,0.18)]">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-pink-50 text-pink-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-10 w-10">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12.001 4.529c2.349-2.532 6.15-2.533 8.498-.001 2.41 2.6 2.41 6.815 0 9.416l-7.66 8.266a1.14 1.14 0 0 1-1.677 0l-7.66-8.266c-2.41-2.601-2.41-6.817 0-9.416 2.348-2.532 6.149-2.531 8.499.001Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 9-6 6" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 6 6" />
                                    </svg>
                                </div>

                                <h4 id="favorite-unfavorite-modal-title" class="mt-6 text-[2rem] font-bold tracking-tight text-slate-800">確定取消收藏？</h4>
                                <p class="mt-4 text-lg leading-8 text-slate-400">
                                    取消後，此貼文將從您的收藏夾中移除。
                                </p>

                                <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <button type="button" id="favorite-unfavorite-cancel" class="inline-flex items-center justify-center rounded-2xl bg-slate-100 px-3 py-3 text-2xl font-bold text-slate-600 transition hover:bg-slate-200">
                                        取消
                                    </button>
                                    <button type="button" id="favorite-unfavorite-confirm" class="inline-flex items-center justify-center rounded-2xl bg-pink-600 px-3 py-3 text-2xl font-bold text-white transition hover:bg-pink-700 disabled:cursor-not-allowed disabled:opacity-70">
                                        確定移除
                                    </button>
                                </div>
                            </div>
                    </div>
                    <!-- 通知中心區塊頭-->

<div id="choiceModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4 text-center">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeChoiceModal()"></div>
        <div class="relative transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-xl transition-all sm:w-full sm:max-w-sm">
            <h3 class="text-center text-lg font-bold text-gray-900 mb-4">請選擇跟單方式</h3>
            <div class="flex flex-col gap-3">
                <button id="followHereBtn" class="w-full rounded-xl bg-pink-500 py-3 font-bold text-white shadow-sm hover:bg-pink-600 transition">
                    就在這裡跟單
                </button>
                <a id="goToHomeBtn" href="#" class="w-full rounded-xl border border-gray-200 bg-white py-3 text-center font-bold text-gray-700 hover:bg-gray-50 transition">
                    前往首頁搜尋此貼文
                </a>
                <button onclick="closeChoiceModal()" class="text-sm text-gray-400 hover:text-gray-600 mt-2">取消</button>
            </div>
        </div>
    </div>
</div>

@foreach($favoriteAgentPosts as $favoriteAgentPost)
<div id="followOrderModal-{{ $favoriteAgentPost->id }}" class="fixed inset-0 z-[70] hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" onclick="closeOrderModal('{{ $favoriteAgentPost->id }}')"></div>
        <div class="relative w-full max-w-2xl transform overflow-hidden rounded-3xl bg-white shadow-2xl transition-all">
            <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800">確認跟單商品</h3>
                <button onclick="closeOrderModal('{{ $favoriteAgentPost->id }}')" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>

            <form action="{{ route('orders.store', $favoriteAgentPost) }}" method="POST" class="p-6">
                @csrf
                <div class="max-h-[50vh] overflow-y-auto space-y-4 pr-2">
                    @foreach($favoriteAgentPost->products as $product)
                        @php
                            $remaining = ($product->max_quantity ?? 0) - ($product->sold_quantity ?? 0);
                        @endphp
                        <div class="product-row flex items-center gap-4 rounded-2xl border p-4 bg-white" data-price="{{ $product->price }}">
                            <img src="{{ $product->display_image_url ?? 'https://via.placeholder.com/60' }}" class="h-14 w-14 rounded-xl object-cover">
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-800 truncate text-sm">{{ $product->name }}</p>
                                <p class="text-pink-500 font-bold text-sm">${{ number_format($product->price) }}</p>
                            </div>
                            <div class="text-center px-2">
                                <span class="block text-[10px] text-gray-400 font-bold">可下單</span>
                                <span class="text-xs font-bold {{ $remaining > 0 ? 'text-blue-500' : 'text-red-500' }}">
                                    {{ $remaining > 0 ? $remaining : '已售罄' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 bg-gray-50 rounded-full p-1 border">
                                <input type="number" name="products[{{ $product->id }}][quantity]" class="qty-input w-10 border-0 bg-transparent text-center font-bold focus:ring-0 text-sm" value="0" min="0" max="{{ $remaining }}" {{ $remaining <= 0 ? 'disabled' : '' }}>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 pt-4 border-t flex flex-col items-end gap-4">
                    <div class="text-right">
                        <span class="text-gray-500 mr-2 text-sm">總計金額：</span>
                        <span class="text-xl font-black text-green-600">NT$ <span class="total-amount">0</span></span>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-pink-500 py-3 font-bold text-white shadow-lg shadow-pink-200 hover:bg-pink-600 transition">確認下單</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
