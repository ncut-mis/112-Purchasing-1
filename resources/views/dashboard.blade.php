<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('會員專區') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

           
            <!-- 頂部統計概覽 -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                    <div class="text-sm text-gray-500 mb-1">進行中的請購</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['ongoing_requests'] }}</div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                    <div class="text-sm text-gray-500 mb-1">未讀訊息</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['unread_messages'] }}</div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-yellow-500">
                    <div class="text-sm text-gray-500 mb-1">收藏貼文</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['favorite_posts'] }}</div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500">
                    <div class="text-sm text-gray-500 mb-1">我的評價</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['reviews_score'] }}</div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-6">

               

                <!-- 左側功能選單 -->
                <div class="w-full md:w-64 space-y-2">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-4 border-b bg-gray-50 font-bold text-gray-700">功能列表</div>

                        @php
                            // 取得當前使用者的代購申請紀錄
                            $app = Auth::user()->agentApplication;
                        @endphp

                        <nav class="p-2 space-y-1">
                            <a href="{{ route('dashboard', ['section' => 'request-lists']) }}" class="flex items-center space-x-3 p-3 rounded-lg {{ $currentSection === 'request-lists' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-600 hover:bg-gray-50 transition' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                <span>請購清單</span>
                            </a>

                            <a href="{{ route('dashboard', ['section' => 'favorite-posts']) }}" class="flex items-center space-x-3 p-3 rounded-lg {{ $currentSection === 'favorite-posts' ? 'bg-pink-50 text-pink-600 font-medium' : 'text-gray-600 hover:bg-gray-50 transition' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                <span>收藏貼文</span>
                            </a>

                            <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                <span>通知中心</span>
                            </a>

                            <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                <span>跟單紀錄</span>
                            </a>

                            <!-- 聊天 (新移動位置) -->

                             <a href="{{ route('messages.index') }}" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                </svg>

                                <span>聊天訊息</span>
                            </a>


                            <!-- 歷史紀錄 (新移動位置) -->
                            <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>歷史紀錄</span>
                            </a>

                            <!-- 評價 (新移動位置) -->
                            <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                <span>評價中心</span>
                            </a>

                            <!-- 動態身分判斷區域 -->
                            <div class="border-t mt-2 pt-2">
                                @if($app && $app->status == 'approved')
                                    <!-- 1. 審核通過：顯示進入代購大廳入口 -->
                                    <a href="{{ route('agent.dashboard') }}" class="flex items-center space-x-3 p-3 rounded-lg text-indigo-700 bg-indigo-50 font-bold transition group">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                        <span class="group-hover:underline">代購人頁面</span>
                                    </a>

                                @elseif($app && $app->status == 'pending')
                                    <!-- 2. 審核中：顯示狀態提示，導向進度頁面 -->
                                    <a href="{{ route('agent.status') }}" class="flex items-center space-x-3 p-3 rounded-lg text-amber-600 bg-amber-50 transition group">
                                        <svg class="w-5 h-5 text-amber-500 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="font-medium group-hover:underline">申請審核中...</span>
                                    </a>
                                @else
                                    <!-- 3. 未申請或被拒絕：顯示申請按鈕 -->
                                    <a href="{{ route('agent.apply') }}" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 transition group">
                                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="font-medium group-hover:underline">申請代購人</span>
                                    </a>

                                @endif
                            </div>
                        </nav>
                    </div>
                </div>



                <!-- 右側主內容區 -->
                <div class="flex-1 space-y-6">

                    @if($currentSection === 'favorite-posts')
                        <div class="bg-white rounded-2xl shadow-sm p-6">
                            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">目前收藏貼文</h3>
                                    <p class="mt-1 text-sm text-gray-500">這裡會顯示你在「最新代購連線」中收藏的代購人貼文。</p>
                                </div>

                                <div class="flex items-center gap-4">                        

                                <!-- 搜尋框 -->

                                <form method="GET" action="{{ route('dashboard') }}" style="display: flex; gap: 8px; min-width: 280px;">
                                    <input type="hidden" name="section" value="favorite-posts">
                                    <input
                                        type="search"
                                        name="favorite_search"
                                        placeholder="搜尋貼文標題、代購人..."
                                        value="{{ request('favorite_search') }}"
                                        style="padding: 8px 12px; border: 2px solid #0e0e0f; border-radius: 8px; font-size: 14px; min-width: 220px; flex: 1;"
                                    >
                                    <button type="submit" style="padding: 8px 16px; background: #ec4899; color: white; border: none; border-radius: 8px; cursor: pointer;">
                                        🔍
                                    </button>
                                </form>
                            </div>
                            </div>

                            @if(request('favorite_search'))
                                <div class="mb-4 rounded-lg border border-pink-200 bg-pink-50 px-4 py-3 text-sm text-pink-700">
                                    搜尋「{{ request('favorite_search') }}」找到 {{ $favoriteAgentPosts->total() }} 筆收藏貼文。
                                    <a href="{{ route('dashboard', ['section' => 'favorite-posts']) }}" class="ml-2 font-semibold hover:underline">清除搜尋</a>
                                </div>
                            @endif

                            <div class="space-y-4" id="favorite-post-list">
                                @forelse($favoriteAgentPosts as $favoriteAgentPost)
                                    <article class="favorite-post-item flex flex-col gap-4 rounded-[26px] border border-pink-100 bg-[#fff8fc] px-5 py-4 shadow-sm lg:flex-row lg:items-center" data-agent-post-id="{{ $favoriteAgentPost->id }}">
                                        <div class="h-24 w-24 shrink-0 overflow-hidden rounded-[26px] bg-white shadow-sm">
                                            @php
                                                $favoriteFirstProduct = $favoriteAgentPost->products->first();
                                                $favoriteImage = optional($favoriteFirstProduct)->display_image_url;
                                            @endphp
                                            @if($favoriteImage)
                                                <img src="{{ $favoriteImage }}" alt="{{ $favoriteAgentPost->title }}" class="h-full w-full object-cover">
                                            @elseif($favoriteAgentPost->cover_image)
                                                <img src="{{ asset('storage/' . $favoriteAgentPost->cover_image) }}" alt="{{ $favoriteAgentPost->title }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center bg-white text-2xl text-pink-300">♡</div>
                                            @endif
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                                <div class="min-w-0 flex-1">
                                                    <h4 class="truncate text-[1.45rem] font-bold tracking-tight text-slate-800">{{ $favoriteAgentPost->title }}</h4>
                                                    <p class="mt-3 text-[1.05rem] text-slate-600">代購人：{{ optional($favoriteAgentPost->user)->name ?? '匿名代購人' }}</p>
                                                    <div class="mt-3 flex flex-wrap items-center gap-x-6 gap-y-2 text-[0.98rem] text-slate-500">
                                                        <span>貼文建立：{{ optional($favoriteAgentPost->created_at)->format('Y-m-d') }}</span>
                                                        <span>可代購商品：{{ $favoriteAgentPost->products->count() }} 項</span>
                                                        <span>狀態：{{ $favoriteAgentPost->status === 'open' ? '接單中' : $favoriteAgentPost->status }}</span>
                                                    </div>
                                                </div>

                                                <div class="flex items-center justify-between gap-4 lg:justify-end">
                                                    <a href="{{ route('agent.posts.search', ['search' => $favoriteAgentPost->title]) }}" class="shrink-0 text-[1.1rem] font-semibold text-pink-500 transition hover:text-pink-600 hover:underline">
                                                        前往首頁
                                                    </a>

                                                    <button
                                                        type="button"
                                                        class="dashboard-favorite-toggle inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-full border border-pink-100 bg-white text-pink-500 shadow-[0_10px_30px_rgba(236,72,153,0.12)] transition hover:-translate-y-0.5 hover:bg-pink-50"
                                                        data-agent-post-id="{{ $favoriteAgentPost->id }}"
                                                        aria-label="取消收藏貼文"
                                                        aria-pressed="true"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-7 w-7">
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
                                            目前尚未收藏任何代購貼文，請先到首頁的「最新代購連線」按下愛心收藏。
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
                    @else
                    <!-- 請購清單塊-->
                         <div class="flex justify-between items-center mb-6">

                            <h3 class="text-lg font-bold text-gray-800">目前請購清單</h3>

                            <div class="flex items-center gap-4">                        

                                <!-- 搜尋框 -->

                                <form method="GET" action="{{ route('dashboard') }}" style="display: flex; gap: 8px; min-width: 280px;">
                                    <input type="hidden" name="section" value="request-lists">
                                    <input
                                        type="search"
                                        name="request_search"
                                        placeholder="搜尋標題、描述、狀態..."
                                        value="{{ request('request_search') }}"
                                        style="padding: 8px 12px; border: 2px solid #0e0e0f; border-radius: 8px; font-size: 14px; min-width: 220px; flex: 1;"
                                    >
                                    <button type="submit" style="padding: 8px 16px; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer;">
                                        🔍
                                    </button>
                                </form>

                                   <a href="{{ route('request-list.create') }}" class="text-sm text-green-600 hover:underline">+ 建立請購清單</a>

                            </div>

                        </div>



                        <!-- 搜尋結果提示 -->
                        @if(request('request_search'))
                            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                                🔍 搜尋「{{ request('request_search') }}」找到 {{ $requestLists->total() ?? 0 }} 筆清單
                                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline ml-2">清除搜尋</a>
                            </div>
                        @endif

                
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="text-gray-400 text-sm border-b">
                                        <th class="pb-3 font-medium">商品</th>
                                        <th class="pb-3 font-medium">國家</th>
                                        <th class="pb-3 font-medium">截止日</th>
                                        <th class="pb-3 font-medium">狀態</th>
                                        <th class="pb-3 font-medium text-right">操作</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y">
                                    @forelse($requestLists ?? [] as $requestList)
                                        @php

                                            $items = $requestList->items ?? collect();

                                            $formatItemLabel = function ($item) {
                                                $name = $item->name ?? '未命名商品';
                                                $qty = (int) ($item->quantity ?? 1);

                                                return $name . '×' . $qty;
                                            };

                                            $firstItem = $items->isNotEmpty()
                                                ? $formatItemLabel($items->first())
                                                : $requestList->title;

                                            $extraItems = $items->slice(1);

                                            $countryLabel = [

                                                'jp' => '日本',

                                                'kr' => '韓國',

                                                'us' => '美國',

                                                'gb' => '英國',

                                            ][$requestList->country] ?? $requestList->country;

                                            $statusLabel = [

                                                'editing' => '編輯中',
                                                'pending' => '等待接單',

                                                'offered' => '代購人已關注',

                                                'matched' => '已確認代購人',

                                                'completed' => '訂單已完成',

                                                'cancelled' => '訂單已取消',

                                            ][$requestList->status] ?? $requestList->status;

                                            $statusClass = [

                                                'editing' => 'bg-slate-100 text-slate-700',
                                                'pending' => 'bg-yellow-100 text-yellow-700',

                                                'offered' => 'bg-blue-100 text-blue-700',

                                                'matched' => 'bg-green-100 text-green-700',

                                                'completed' => 'bg-emerald-100 text-emerald-700',

                                                'cancelled' => 'bg-gray-200 text-gray-600',

                                            ][$requestList->status] ?? 'bg-gray-100 text-gray-700';

                                        @endphp

                                        <tr class="text-sm align-top">
                                            <td class="py-4 font-medium text-gray-800">
                                                @if($extraItems->isNotEmpty())
                                                    <details class="group">
                                                        <summary class="cursor-pointer select-none hover:text-blue-600">
                                                            {{ $firstItem }}
                                                            <span class="text-xs text-gray-400">（另有 {{ $extraItems->count() }} 項）</span>
                                                        </summary>
                                                        <ul class="mt-2 ml-4 list-disc text-gray-500 text-xs space-y-1">
                                                            @foreach($extraItems as $item)
                                                                <li>{{ $formatItemLabel($item) }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </details>
                                                @else
                                                    {{ $firstItem }}
                                                @endif
                                            </td>

                                            <td class="py-4 text-gray-500">{{ $countryLabel }}</td>
                                            <td class="py-4 text-gray-800">{{ optional($requestList->deadline)->format('Y-m-d') ?? '-' }}</td>
                                            <td class="py-4">
                                                <span class="px-2 py-1 rounded-full text-[10px] {{ $statusClass }}">{{ $statusLabel }}</span>
                                            </td>
                                            <td class="py-4 text-right">
                                                @php
                                                    $acceptedOffer = $requestList->offers->firstWhere('status', 'accepted');
                                                    $activeOffer = $acceptedOffer ?? $requestList->offers->first();
                                                @endphp

                                                <div class="inline-flex items-center gap-3">
                                                    @if($requestList->status === 'matched')
                                                        <button class="text-gray-500 hover:underline">檢視</button>
                                                    @elseif($requestList->status === 'editing')
                                                        <button type="button" class="text-blue-500 hover:underline" onclick="openEditModal({{ $requestList->id }})">編輯</button>
                        
                                                        <form method="POST" action="{{ route('request-list.destroy', $requestList) }}" onsubmit="return confirm('確定要刪除此請購清單嗎？');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-500 hover:underline">刪除</button>
                                                        </form>

                                                        <form method="POST" action="{{ route('request-list.submit', $requestList) }}" onsubmit="return confirm('送出後清單將無法修改與刪除,確定送出嗎？');">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="text-green-600 hover:underline">送出</button>
                                                        </form>
                                                    @elseif($requestList->status === 'pending')
                                                        <button type="button" class="inline-flex items-center rounded-lg bg-blue-500 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-600" onclick="openRequestDetailModal({{ $requestList->id }})">檢視</button>
                                                        <button type="button" class="inline-flex items-center rounded-lg bg-green-400 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-green-500">聊天</button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>

                                    @empty

                                        <tr>
                                            <td colspan="5" class="py-6 text-center text-gray-400">
                                                @if(request('request_search'))
                                                    沒有找到「{{ request('request_search') }}」相關的請購清單
                                                @else
                                                    目前尚未建立請購清單
                                                @endif
                                            </td>
                                        </tr>

                                    @endforelse

                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif



                        @foreach($requestLists ?? [] as $requestList)
                            @if($requestList->status === 'pending')
                                @php
                                    $modalCountryLabel = [
                                        'jp' => '日本',
                                        'kr' => '韓國',
                                        'us' => '美國',
                                        'gb' => '英國',
                                    ][$requestList->country] ?? $requestList->country;
                                    $acceptedOffer = $requestList->offers->firstWhere('status', 'accepted');
                                    $activeOffer = $acceptedOffer ?? $requestList->offers->first();
                                    $activeAgent = optional($activeOffer)->agent;
                                    $agentAvatar = $activeAgent && $activeAgent->avatar
                                        ? asset('storage/' . $activeAgent->avatar)
                                        : ($activeAgent
                                            ? 'https://ui-avatars.com/api/?name=' . urlencode($activeAgent->name) . '&background=2563eb&color=fff&size=128'
                                            : null);
                                @endphp

                                <div id="request-detail-modal-{{ $requestList->id }}" class="request-detail-modal hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4 py-4" onclick="handleRequestDetailBackdrop(event, {{ $requestList->id }})">
                                    <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                                        <div class="flex items-start justify-between gap-4 border-b border-slate-200 bg-gradient-to-r from-blue-600 to-cyan-500 px-5 py-4 text-white">
                                            <div>
                                                <p class="text-sm font-medium text-blue-100">等待接單中的請購清單</p>
                                                <h4 class="mt-1 text-2xl font-bold">{{ $requestList->title }}</h4>
                                                <p class="mt-2 text-sm text-blue-50">可查看商品明細與目前接單狀況。</p>
                                            </div>
                                            <button type="button" class="rounded-full bg-white/15 p-2 text-white transition hover:bg-white/25" onclick="closeRequestDetailModal({{ $requestList->id }})" aria-label="關閉檢視視窗">✕</button>
                                        </div>

                                        <div class="max-h-[72vh] overflow-y-auto px-5 py-5">
                                            <div class="grid gap-4 lg:grid-cols-[minmax(0,1.6fr)_minmax(220px,0.9fr)]">
                                                <div class="space-y-4">
                                                    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                        <h5 class="text-base font-bold text-slate-800">商品明細</h5>
                                                        <div class="mt-3 space-y-3">
                                                            @forelse($requestList->items as $item)
                                                                <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:flex-row sm:items-center">
                                                                    <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-xl bg-slate-100">
                                                                        @if($item->reference_image)
                                                                            <img src="{{ url('/request-item-image/' . $item->id) }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                                                        @else
                                                                            <span class="text-xs text-slate-400">無商品圖片</span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                                                            <div>
                                                                                <p class="text-base font-semibold text-slate-800">{{ $item->name }}</p>
                                                                                <p class="mt-1 text-sm text-slate-500">數量：{{ $item->quantity }}</p>
                                                                            </div>
                                                                            @if($item->reference_url)
                                                                                <a href="{{ $item->reference_url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">參考連結</a>
                                                                            @endif
                                                                        </div>
                                                                        @if($item->specification)
                                                                            <p class="mt-2 rounded-xl bg-slate-50 px-3 py-2 text-sm text-slate-600">規格／補充：{{ $item->specification }}</p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center text-sm text-slate-400">目前沒有商品資料。</div>
                                                            @endforelse
                                                        </div>
                                                    </section>

                                                    <section class="grid gap-3 md:grid-cols-2">
                                                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                                            <h5 class="text-base font-bold text-slate-800">商家與購買資訊</h5>
                                                            <dl class="mt-3 space-y-3 text-sm">
                                                                <div class="flex items-start justify-between gap-4">
                                                                    <dt class="text-slate-500">國家</dt>
                                                                    <dd class="text-right font-medium text-slate-800">{{ $modalCountryLabel }}</dd>
                                                                </div>
                                                                <div class="flex items-start justify-between gap-4">
                                                                    <dt class="text-slate-500">店家</dt>
                                                                    <dd class="text-right font-medium text-slate-800">{{ $requestList->title ?: '未提供' }}</dd>
                                                                </div>
                                                                <div>
                                                                    <dt class="text-slate-500">店家詳細地址</dt>
                                                                    <dd class="mt-1 rounded-xl bg-slate-50 px-3 py-3 leading-6 text-slate-700">{{ $requestList->detail_address ?: '未提供詳細地址' }}</dd>
                                                                </div>
                                                                <div class="flex items-start justify-between gap-4">
                                                                    <dt class="text-slate-500">商品截止日</dt>
                                                                    <dd class="text-right font-medium text-slate-800">{{ optional($requestList->deadline)->format('Y-m-d') ?? '未提供' }}</dd>
                                                                </div>
                                                            </dl>
                                                        </div>

                                                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                                            <h5 class="text-base font-bold text-slate-800">備註</h5>
                                                            <dl class="mt-3 space-y-3 text-sm">
                                                                <div>
                                                                    <dt class="text-slate-500">內容</dt>
                                                                    <dd class="mt-1 rounded-xl bg-slate-50 px-3 py-3 leading-6 text-slate-700">{{ $requestList->note ?: '目前沒有備註。' }}</dd>
                                                                </div>
                                                            </dl>
                                                        </div>
                                                    </section>
                                                </div>

                                                <aside class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                    <h5 class="text-base font-bold text-slate-800">目前接單狀況</h5>
                                                    @if($activeAgent)
                                                        <div class="mt-4 rounded-2xl bg-white p-4 text-center shadow-sm">
                                                            <div class="mx-auto flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-4 border-blue-100 bg-slate-100">
                                                                <img src="{{ $agentAvatar }}" alt="{{ $activeAgent->name }}" class="h-full w-full object-cover">
                                                            </div>
                                                            <p class="mt-3 text-base font-semibold text-slate-800">{{ $activeAgent->name }}</p>
                                                            <p class="mt-1 text-sm text-slate-500">{{ $acceptedOffer ? '已確認接單的代購人' : '已有代購人提出接單意願' }}</p>
                                                        </div>
                                                    @else
                                                        <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-6 text-center">
                                                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-2xl">🕒</div>
                                                            <p class="mt-4 text-sm font-medium text-slate-700">目前尚未有代購人接單</p>
                                                            <p class="mt-2 text-xs leading-5 text-slate-400">當有代購人接單或提出報價時，這裡會顯示對方的頭像與名稱。</p>
                                                        </div>
                                                    @endif
                                                </aside>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($requestList->status === 'editing')
                                <div id="edit-modal-{{ $requestList->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
                                    <div class="bg-white w-full max-w-3xl rounded-xl shadow-lg max-h-[90vh] overflow-y-auto">
                                        <div class="flex justify-between items-center border-b px-6 py-4">
                                            <h4 class="text-lg font-bold text-gray-800">編輯請購清單</h4>
                                            <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeEditModal({{ $requestList->id }})">✕</button>
                                        </div>

                                        <form action="{{ route('request-list.update', $requestList) }}" method="POST" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
                                            @csrf
                                            @method('PUT')

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">國家</label>
                                                    <select name="country" class="w-full border-gray-300 rounded-lg">
                                                        <option value="jp" @selected($requestList->country === 'jp')>日本</option>
                                                        <option value="kr" @selected($requestList->country === 'kr')>韓國</option>
                                                        <option value="us" @selected($requestList->country === 'us')>美國</option>
                                                        <option value="gb" @selected($requestList->country === 'gb')>英國</option>
                                                    </select>

                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">商品截止日</label>
                                                    <input type="date" name="deadline" value="{{ optional($requestList->deadline)->format('Y-m-d') }}" class="w-full border-gray-300 rounded-lg">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">店家</label>
                                                    <input type="text" name="store_name" value="{{ $requestList->title }}" class="w-full border-gray-300 rounded-lg" placeholder="請輸入店家名稱">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">詳細地址</label>
                                                    <input type="text" name="detail_address" value="{{ $requestList->detail_address }}" class="w-full border-gray-300 rounded-lg" placeholder="請輸入詳細地址">
                                                </div>
                                            </div>


                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">備註</label>
                                                <textarea name="note" class="w-full border-gray-300 rounded-lg" rows="2" placeholder="可填寫代購需求補充、規格、交付注意事項等">{{ $requestList->note }}</textarea>
                                            </div>

                                            <div class="space-y-4 pt-2 edit-items-wrapper" data-request-list-id="{{ $requestList->id }}" data-max-items="3" data-next-index="{{ $requestList->items->count() }}">
                                                <div class="flex items-center justify-between gap-3">
                                                    <h5 class="font-semibold text-gray-800">商品資料</h5>
                                                    <button type="button" class="px-3 py-2 text-sm rounded-lg border border-green-200 text-green-600 hover:bg-green-50 disabled:opacity-50 disabled:cursor-not-allowed edit-add-item-btn" onclick="addEditItem({{ $requestList->id }})">新增商品</button>
                                                </div>
                                                <p class="text-xs text-gray-500 edit-item-limit-hint">最多可保留 3 項商品。</p>

                                                <div class="space-y-3 edit-item-list">
                                                    @foreach($requestList->items->take(3) as $index => $item)
                                                        <div class="border rounded-lg p-3 space-y-2 edit-item-card" data-existing="1">
                                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                            <input type="hidden" name="items[{{ $index }}][remove]" value="0" class="remove-flag">
                                                            <div class="flex items-center justify-between">
                                                                <label class="block text-sm font-medium text-gray-700">商品名稱</label>
                                                                <button type="button" class="text-xs text-red-500 hover:underline" onclick="removeEditItem(this)">刪除此商品</button>
                                                            </div>

                                                            <div>
                                                                <input type="text" name="items[{{ $index }}][item_name]" value="{{ $item->name }}" class="w-full border-gray-300 rounded-lg" required>
                                                            </div>

                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">數量</label>
                                                                <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="1" step="1" class="w-full border-gray-300 rounded-lg" required>
                                                            </div>

                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-1">商品圖片</label>
                                                                @if($item->reference_image)
                                                                    <img src="{{ url('/request-item-image/' . $item->id) }}" alt="商品圖片" class="w-24 h-24 object-cover rounded border mb-2">
                                                                    <p class="text-xs text-gray-500 mb-2">未重新上傳會保留原圖片</p>
                                                                @endif
                                                                <input type="file" name="items[{{ $index }}][item_image]" class="w-full border-gray-300 rounded-lg" accept="image/*">
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <template id="edit-item-template-{{ $requestList->id }}">
                                                    <div class="border rounded-lg p-3 space-y-2 edit-item-card" data-existing="0">
                                                        <input type="hidden" name="items[__INDEX__][remove]" value="0" class="remove-flag">
                                                        <div class="flex items-center justify-between">
                                                            <label class="block text-sm font-medium text-gray-700">商品名稱</label>
                                                            <button type="button" class="text-xs text-red-500 hover:underline" onclick="removeEditItem(this)">刪除此商品</button>
                                                        </div>

                                                        <div>
                                                            <input type="text" name="items[__INDEX__][item_name]" class="w-full border-gray-300 rounded-lg" placeholder="請輸入商品名稱" required>
                                                        </div>

                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">數量</label>
                                                            <input type="number" name="items[__INDEX__][quantity]" value="1" min="1" step="1" class="w-full border-gray-300 rounded-lg" required>
                                                        </div>

                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">商品圖片</label>
                                                            <input type="file" name="items[__INDEX__][item_image]" class="w-full border-gray-300 rounded-lg" accept="image/*">
                                                        </div>
                                                    </div>
                                                </template>

                                            </div>



                                            <div class="flex justify-end gap-2 pt-2">
                                                <button type="button" class="px-4 py-2 rounded-lg border text-gray-600" onclick="closeEditModal({{ $requestList->id }})">取消</button>
                                                <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white">確認</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            @endif

                        @endforeach
                </div>

            </div>

        </div>

    </div>

    <script>
        const dashboardFavoriteToggleUrl = @json(route('favorite.toggle'));
        const dashboardCsrfToken = @json(csrf_token());

        function openRequestDetailModal(id) {
            const modal = document.getElementById(`request-detail-modal-${id}`);

            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeRequestDetailModal(id) {
            const modal = document.getElementById(`request-detail-modal-${id}`);

            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            if (favoriteUnfavoriteModal && !favoriteUnfavoriteModal.classList.contains('hidden')) {
                closeFavoriteUnfavoriteModal();
                return;
            }

            document.querySelectorAll('.request-detail-modal').forEach((modal) => {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            });
        });

        function handleRequestDetailBackdrop(event, id) {
            if (event.target.id === `request-detail-modal-${id}`) {
                closeRequestDetailModal(id);
            }
        }

        const favoriteUnfavoriteModal = document.getElementById('favorite-unfavorite-modal');
        const favoriteUnfavoriteCancelButton = document.getElementById('favorite-unfavorite-cancel');
        const favoriteUnfavoriteConfirmButton = document.getElementById('favorite-unfavorite-confirm');
        let pendingFavoriteRemovalButton = null;
        const favoriteEmptyStateHtml = '<div class="rounded-2xl border border-dashed border-pink-200 bg-pink-50/40 px-6 py-12 text-center text-sm text-gray-500">目前尚未收藏任何代購貼文，請先到首頁的「最新代購連線」按下愛心收藏。</div>';

        function closeFavoriteUnfavoriteModal() {
            if (!favoriteUnfavoriteModal) {
                return;
            }

            favoriteUnfavoriteModal.classList.add('hidden');
            pendingFavoriteRemovalButton = null;
            document.body.classList.remove('overflow-hidden');

            if (favoriteUnfavoriteConfirmButton) {
                favoriteUnfavoriteConfirmButton.disabled = false;
                favoriteUnfavoriteConfirmButton.textContent = '確定移除';
            }
        }

        function openFavoriteUnfavoriteModal(button) {
            if (!favoriteUnfavoriteModal || !button) {
                return;
            }

            pendingFavoriteRemovalButton = button;
            favoriteUnfavoriteModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        async function removeFavoriteFromDashboard(button) {
            const agentPostId = button?.dataset.agentPostId;
            if (!agentPostId || button.disabled) {
                return;
            }

            button.disabled = true;
            if (favoriteUnfavoriteConfirmButton) {
                favoriteUnfavoriteConfirmButton.disabled = true;
                favoriteUnfavoriteConfirmButton.textContent = '移除中...';
            }

            try {
                const response = await fetch(dashboardFavoriteToggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': dashboardCsrfToken,
                    },
                    body: JSON.stringify({
                        type: 'agent_post',
                        id: agentPostId,
                    }),
                });

                if (!response.ok) {
                    throw new Error('favorite toggle failed');
                }

                const data = await response.json();
                if (data.status === 'removed') {
                    const card = button.closest('.favorite-post-item');
                    card?.remove();

                    const list = document.getElementById('favorite-post-list');
                    if (list && !list.querySelector('.favorite-post-item')) {
                        list.innerHTML = favoriteEmptyStateHtml;
                    }
                }

                closeFavoriteUnfavoriteModal();
            } catch (error) {
                console.error(error);
                alert('更新收藏狀態失敗，請稍後再試。');

                if (favoriteUnfavoriteConfirmButton) {
                    favoriteUnfavoriteConfirmButton.disabled = false;
                    favoriteUnfavoriteConfirmButton.textContent = '確定移除';
                }

                button.disabled = false;
            }
        }

        document.querySelectorAll('.dashboard-favorite-toggle').forEach((button) => {
            button.addEventListener('click', () => {
                const agentPostId = button.dataset.agentPostId;
                if (!agentPostId || button.disabled) {
                    return;
                }

                openFavoriteUnfavoriteModal(button);
            });
        });

        favoriteUnfavoriteCancelButton?.addEventListener('click', closeFavoriteUnfavoriteModal);
        favoriteUnfavoriteModal?.addEventListener('click', (event) => {
            if (event.target === favoriteUnfavoriteModal) {
                closeFavoriteUnfavoriteModal();
            }
        });
        favoriteUnfavoriteConfirmButton?.addEventListener('click', () => {
            if (pendingFavoriteRemovalButton) {
                removeFavoriteFromDashboard(pendingFavoriteRemovalButton);
            }
        });


        function openEditModal(id) {
            const modal = document.getElementById(`edit-modal-${id}`);

            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

            }

        }



        function getEditItemsWrapperByListId(id) {
            return document.querySelector(`.edit-items-wrapper[data-request-list-id="${id}"]`);
        }

        function getVisibleEditCards(wrapper) {
            return wrapper ? wrapper.querySelectorAll('.edit-item-card:not(.hidden)') : [];
        }

        function updateEditItemUi(wrapper) {
            if (!wrapper) return;

            const maxItems = parseInt(wrapper.dataset.maxItems || '3', 10);
            const visibleCards = Array.from(getVisibleEditCards(wrapper));
            const addBtn = wrapper.querySelector('.edit-add-item-btn');
            const hint = wrapper.querySelector('.edit-item-limit-hint');

            visibleCards.forEach((card) => {
                const removeBtn = card.querySelector('button[onclick="removeEditItem(this)"]');
                if (removeBtn) {
                    removeBtn.disabled = visibleCards.length <= 1;
                    removeBtn.classList.toggle('opacity-50', visibleCards.length <= 1);
                    removeBtn.classList.toggle('cursor-not-allowed', visibleCards.length <= 1);
                }
            });

            const remaining = maxItems - visibleCards.length;
            if (addBtn) {
                addBtn.disabled = remaining <= 0;
            }

            if (hint) {
                hint.textContent = remaining > 0
                    ? `還可再新增 ${remaining} 項商品。`
                    : '已達商品上限（最多 3 項）。';
            }
        }

        function addEditItem(id) {
            const wrapper = getEditItemsWrapperByListId(id);
            if (!wrapper) return;

            const maxItems = parseInt(wrapper.dataset.maxItems || '3', 10);
            const visibleCards = getVisibleEditCards(wrapper);
            if (visibleCards.length >= maxItems) {
                return;
            }

            const template = document.getElementById(`edit-item-template-${id}`);
            const list = wrapper.querySelector('.edit-item-list');
            if (!template || !list) return;

            const nextIndex = parseInt(wrapper.dataset.nextIndex || String(visibleCards.length), 10);
            const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex));
            list.insertAdjacentHTML('beforeend', html);
            wrapper.dataset.nextIndex = String(nextIndex + 1);

            updateEditItemUi(wrapper);
        }

        function removeEditItem(button) {
            const card = button.closest('.edit-item-card');
            if (!card) return;

            const wrapper = button.closest('.edit-items-wrapper');
            if (!wrapper) return;

            const visibleCards = getVisibleEditCards(wrapper);
            if (visibleCards.length <= 1) {
                alert('至少需保留一項商品');
                return;
            }

            const isExisting = card.dataset.existing === '1';
            const flag = card.querySelector('.remove-flag');

            if (isExisting && flag) {
                flag.value = '1';
                card.classList.add('hidden');
            } else {
                card.remove();
            }

            updateEditItemUi(wrapper);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.edit-items-wrapper').forEach(updateEditItemUi);
        });

        function closeEditModal(id) {
            const modal = document.getElementById(`edit-modal-${id}`);
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

    </script>

</x-app-layout>