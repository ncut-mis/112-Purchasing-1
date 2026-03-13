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
                    <div class="text-2xl font-bold text-gray-800">3</div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                    <div class="text-sm text-gray-500 mb-1">未讀訊息</div>
                    <div class="text-2xl font-bold text-gray-800">5</div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-yellow-500">
                    <div class="text-sm text-gray-500 mb-1">收藏貼文</div>
                    <div class="text-2xl font-bold text-gray-800">12</div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500">
                    <div class="text-sm text-gray-500 mb-1">我的評價</div>
                    <div class="text-2xl font-bold text-gray-800">4.9 / 5</div>
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
                            <a href="#" class="flex items-center space-x-3 p-3 rounded-lg bg-green-50 text-green-700 font-medium">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                <span>請購清單</span>
                            </a>

                            <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 transition">
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

                    <!-- 請購清單區塊-->
                                            <div class="flex justify-between items-center mb-6">

                            <h3 class="text-lg font-bold text-gray-800">目前請購清單</h3>

                            <div class="flex items-center gap-4">                        

                                <!-- 搜尋框 -->

                                <form method="GET" action="{{ route('dashboard') }}" style="display: flex; gap: 8px; min-width: 280px;">
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

                                                'pending' => '等待接單',

                                                'offered' => '代購人已關注',

                                                'matched' => '已確認代購人',

                                                'completed' => '訂單已完成',

                                                'cancelled' => '訂單已取消',

                                            ][$requestList->status] ?? $requestList->status;

                                            $statusClass = [

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
                                              <div class="inline-flex items-center gap-3">
                                                    @if($requestList->status === 'matched')
                                                        <button class="text-gray-500 hover:underline">檢視</button>
                                                    @else
                                                        <button type="button" class="text-blue-500 hover:underline" onclick="openEditModal({{ $requestList->id }})">編輯</button>
                                                    @endif

                                                    @if($requestList->status === 'pending')
                                                        <form method="POST" action="{{ route('request-list.destroy', $requestList) }}" onsubmit="return confirm('確定要刪除此請購清單嗎？');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-500 hover:underline">刪除</button>
                                                        </form>
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



                        @foreach($requestLists ?? [] as $requestList)
                            @if($requestList->status !== 'matched')
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
                                                    <input type="text" name="address_detail" value="{{ $requestList->note }}" class="w-full border-gray-300 rounded-lg" placeholder="請輸入詳細地址">
                                                </div>
                                            </div>


                                            <div class="space-y-4 pt-2">

                                                <h5 class="font-semibold text-gray-800">商品資料</h5>

                                                @foreach($requestList->items->take(3) as $index => $item)
                                                    <div class="border rounded-lg p-3 space-y-2 edit-item-card">
                                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                        <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}">
                                                        <input type="hidden" name="items[{{ $index }}][remove]" value="0" class="remove-flag">
                                                        <div class="flex items-center justify-between">
                                                            <label class="block text-sm font-medium text-gray-700">商品名稱</label>
                                                            <button type="button" class="text-xs text-red-500 hover:underline" onclick="removeEditItem(this)">刪除此商品</button>
                                                        </div>

                                                        <div>
                                                            <input type="text" name="items[{{ $index }}][item_name]" value="{{ $item->name }}" class="w-full border-gray-300 rounded-lg">
                                                        </div>

                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-1">數量</label>
                                                            <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="1" step="1" class="w-full border-gray-300 rounded-lg">
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



                                            <div class="flex justify-end gap-2 pt-2">
                                                <button type="button" class="px-4 py-2 rounded-lg border text-gray-600" onclick="closeEditModal({{ $requestList->id }})">取消</button>
                                                <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white">確認</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            @endif

                        @endforeach



                    <!-- 聊天訊息預覽 -->

                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">最新聊天</h3>
                        <div class="space-y-4">
                            <div class="flex items-center gap-4 p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition">
                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold">小</div>
                                <div class="flex-1">
                                    <div class="flex justify-between">
                                        <span class="font-bold text-sm">小王 (日本代購人)</span>
                                        <span class="text-[10px] text-gray-400">10分鐘前</span>
                                    </div>
                                    <p class="text-xs text-gray-500 truncate">您好，手把已經幫您買到囉，稍後提供照片...</p>
                                </div>
                                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <script>

        function openEditModal(id) {
            const modal = document.getElementById(`edit-modal-${id}`);

            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

            }

        }



        function removeEditItem(button) {
            const card = button.closest('.edit-item-card');
            if (!card) return;

            const container = card.parentElement;

            const visibleCards = container.querySelectorAll('.edit-item-card:not(.hidden)');

            if (visibleCards.length <= 1) {
                alert('至少需保留一項商品');
                return;
            }

            const flag = card.querySelector('.remove-flag');

            if (flag) {
                flag.value = '1';
            }

            card.classList.add('hidden');

        }


        function closeEditModal(id) {
            const modal = document.getElementById(`edit-modal-${id}`);
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

    </script>

</x-app-layout>