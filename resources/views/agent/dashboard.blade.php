<x-app-layout>
    @php
        $keyword = $keyword ?? request('q', '');
        $selectedCountry = $selectedCountry ?? request('country', 'all');
        $selectedTime = $selectedTime ?? request('time', 'all');
        $requestLists = $requestLists ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
    @endphp

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-indigo-800 leading-tight">
                {{ __('代購接單大廳') }}
            </h2>

            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('agent.dashboard') }}" class="relative w-full md:w-96 group">
                    <input
                        type="text"
                        name="q"
                        value="{{ $keyword }}"
                        class="w-full pl-5 pr-12 py-3 bg-white border-2 border-indigo-50 rounded-2xl text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-md transition-all duration-300 group-hover:border-indigo-200"
                        placeholder="搜尋商品名稱、國家或備註關鍵字..."
                    >
                    @if($selectedCountry !== 'all')
                        <input type="hidden" name="country" value="{{ $selectedCountry }}">
                    @endif
                    @if($selectedTime !== 'all')
                        <input type="hidden" name="time" value="{{ $selectedTime }}">
                    @endif
                    <div class="absolute inset-y-0 right-0 flex items-center pr-1">
                        <button type="submit" class="bg-indigo-600 text-white p-2 rounded-xl hover:bg-indigo-700 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </div>
                </form>

                <a href="{{ route('agent.member') }}" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent rounded-full text-sm font-bold text-white hover:bg-indigo-700 transition shadow-md">
                    <i class="bi bi-person-badge"></i>
                    <span>會員專區</span>
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $countryLabels = [
            'all' => '全部地區',
            'jp' => '🇯🇵 日本',
            'kr' => '🇰🇷 韓國',
            'us' => '🇺🇸 美國',
            'gb' => '🇬🇧 英國',
        ];
    @endphp

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8 space-y-6">
                <div>
                    <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="bi bi-geo-alt"></i> 依國家篩選
                    </h5>
                    <div class="flex flex-wrap gap-2">
                        @foreach($countryLabels as $code => $label)
                            <a
                                href="{{ route('agent.dashboard', array_filter(['country' => $code === 'all' ? null : $code, 'time' => $selectedTime !== 'all' ? $selectedTime : null, 'q' => $keyword ?: null])) }}"
                                class="px-5 py-2 rounded-full text-sm font-bold transition {{ $selectedCountry === $code ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-50 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600' }}"
                            >
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-50">
                    <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="bi bi-clock-history"></i> 依時間篩選
                    </h5>
                    @php
                        $timeOptions = [
                            'all' => '不限時間',
                            'urgent' => '最緊急 (24H內)',
                            'three_days' => '3天內截止',
                            'this_week' => '本周截止',
                        ];
                    @endphp
                    <div class="flex flex-wrap gap-2">
                        @foreach($timeOptions as $timeKey => $timeLabel)
                            <a
                                href="{{ route('agent.dashboard', array_filter(['country' => $selectedCountry !== 'all' ? $selectedCountry : null, 'time' => $timeKey === 'all' ? null : $timeKey, 'q' => $keyword ?: null])) }}"
                                class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $selectedTime === $timeKey ? 'bg-indigo-600 text-white shadow-md' : 'bg-white border border-gray-200 text-gray-600 hover:border-indigo-500 hover:text-indigo-600' }}"
                            >
                                {{ $timeLabel }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-bold text-gray-800">最新請購需求</h3>
                    <span class="text-sm text-gray-400">找到 {{ $requestLists->total() }} 個符合條件的請購</span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @forelse($requestLists as $requestList)
                        @php
                            $countryCode = $requestList->country;
                            $countryTag = $countryLabels[$countryCode] ?? $countryCode;
                            $firstItem = $requestList->items->first();
                            $title = $requestList->title ?: ($firstItem->name ?? '未命名請購');
                        @endphp

                        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:border-indigo-200 hover:shadow-md transition group">
                            <div class="flex justify-between items-start mb-4 gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-inner shrink-0">
                                        <i class="bi bi-bag-heart-fill text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800 group-hover:text-indigo-600 transition">{{ $title }}</h4>
                                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                                            <span class="text-[10px] px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md font-bold">{{ $countryTag }}</span>
                                            <span class="text-[10px] px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md font-bold">截止：{{ optional($requestList->deadline)->format('Y-m-d') ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $isFavorited = auth()->check() && auth()->user()->hasFavoritedRequestList($requestList->id);
                                @endphp
                                <button
                                    type="button"
                                    class="favorite-toggle w-9 h-9 rounded-full transition flex items-center justify-center {{ $isFavorited ? 'bg-pink-50 text-pink-500' : 'bg-gray-100 text-gray-400' }} hover:bg-pink-50 hover:text-pink-400"
                                    data-request-list-id="{{ $requestList->id }}"
                                    aria-label="收藏請購清單"
                                    aria-pressed="{{ $isFavorited ? 'true' : 'false' }}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                        <path d="M12.001 4.529c2.349-2.532 6.15-2.533 8.498-.001 2.41 2.6 2.41 6.815 0 9.416l-7.66 8.266a1.14 1.14 0 0 1-1.677 0l-7.66-8.266c-2.41-2.601-2.41-6.817 0-9.416 2.348-2.532 6.149-2.531 8.499.001Z"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="mb-5">
                                <p class="text-xs font-bold text-gray-400 mb-2">請購內容</p>
                                <ul class="space-y-1 text-sm text-gray-600">
                                    @foreach($requestList->items as $item)
                                        <li>• {{ $item->name }} × {{ $item->quantity }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            @if($requestList->note)
                                <p class="text-sm text-gray-500 mb-6 leading-relaxed line-clamp-2">備註：{{ $requestList->note }}</p>
                            @endif
                            @if($requestList->detail_address)
                                <p class="text-sm text-gray-500 mb-6 leading-relaxed line-clamp-2">地址：{{ $requestList->detail_address }}</p>
                            @endif

                            <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                                <div class="flex items-center gap-2 min-w-0">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($requestList->user->name ?? 'User') }}&background=f3f4f6" class="w-6 h-6 rounded-full" alt="user-avatar">
                                    <span class="text-xs text-gray-500 truncate">請購人：{{ $requestList->user->name ?? '未知使用者' }}</span>
                                </div>
                                @if(auth()->check() && $requestList->user_id === auth()->id())
                                    <button type="button"class="accept-order-btn px-8 py-2.5 bg-gray-400 text-white rounded-xl font-bold text-sm flex items-center gap-2 cursor-not-allowed" disabled>
                                       <i class="bi bi-cart-plus"></i> 無法接單
                                    </button>
                                @else
                                    @php
                                        $orderData = [
                                            "id" => $requestList->id,
                                            "title" => $title,
                                            "detail_address" => $requestList->detail_address ?: '未填寫',
                                            "deadline" => optional($requestList->deadline)->format("Y-m-d"),
                                            "note" => $requestList->note,
                                            "items" => $requestList->items->map(function($item) {
                                                $img = null;
                                                if ($item->reference_image) {
                                                    $img = url('/request-item-image/' . $item->id);
                                                }
                                                return [
                                                    "name" => $item->name,
                                                    "quantity" => $item->quantity,
                                                    "image" => $img
                                                ];
                                            })->values()->toArray(),
                                        ];
                                    @endphp
                                    <button type="button"
                                        class="accept-order-btn px-8 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm flex items-center gap-2 hover:bg-indigo-700 transition"
                                        data-request-list='@json($orderData)'
                                    >
                                        <i class="bi bi-cart-plus"></i> 我要接單
                                    </button>
                                @endif
                                <!-- 接單詳情 Modal -->
                                <div id="order-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 hidden">
                                    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-2xl text-left relative">
                                        <button id="order-modal-close" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                                        <div class="mb-4">
                                            <h3 class="text-xl font-bold text-indigo-700 mb-2" id="order-modal-title"></h3>
                                            <div class="text-sm text-gray-500 mb-1"><span class="font-bold">地址：</span><span id="order-modal-address"></span></div>
                                            <div class="text-sm text-gray-500 mb-1"><span class="font-bold">截止日期：</span><span id="order-modal-deadline"></span></div>
                                        </div>
                                        <div class="mb-4">
                                            <div class="font-bold text-gray-700 mb-2">商品明細</div>
                                            <div id="order-modal-items" class="space-y-2"></div>
                                        </div>
                                        <div class="mb-4">
                                            <div class="font-bold text-gray-700 mb-1">備註</div>
                                            <div id="order-modal-note" class="text-gray-600 text-sm"></div>
                                        </div>
                                        <div class="flex justify-end">
                                            <button class="px-6 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition">確定接單</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="lg:col-span-2 bg-white rounded-2xl border border-dashed border-gray-200 p-12 text-center text-gray-500">
                            目前沒有符合條件的請購清單。
                        </div>
                    @endforelse
                </div>

                @if(method_exists($requestLists, 'links'))
                    <div>
                        {{ $requestLists->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 接單詳情 Modal
            const orderModal = document.getElementById('order-modal');
            const orderModalClose = document.getElementById('order-modal-close');
            const orderModalTitle = document.getElementById('order-modal-title');
            const orderModalAddress = document.getElementById('order-modal-address');
            const orderModalDeadline = document.getElementById('order-modal-deadline');
            const orderModalItems = document.getElementById('order-modal-items');
            const orderModalNote = document.getElementById('order-modal-note');
            document.querySelectorAll('.accept-order-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const data = JSON.parse(btn.getAttribute('data-request-list'));
                    orderModalTitle.textContent = data.title || '';
                    orderModalAddress.textContent = data.detail_address || '-';
                    orderModalDeadline.textContent = data.deadline || '-';
                    orderModalNote.textContent = data.note || '-';
                    // 商品明細
                    orderModalItems.innerHTML = '';
                    (data.items || []).forEach(function(item) {
                        const div = document.createElement('div');
                        div.className = 'flex items-center gap-4';
                        if (item.image) {
                            div.innerHTML += `<img src="${item.image}" class="w-24 h-24 rounded-xl object-cover border border-gray-200">`;
                        } else {
                            div.innerHTML += `<div class="w-24 h-24 rounded-xl bg-gray-100 flex items-center justify-center text-gray-300 border border-gray-200"><i class='bi bi-image'></i></div>`;
                        }
                        div.innerHTML += `<div><div class='font-bold text-gray-800'>${item.name}</div><div class='text-xs text-gray-500'>數量：${item.quantity}</div></div>`;
                        orderModalItems.appendChild(div);
                    });
                    orderModal.classList.remove('hidden');
                });
            });
            orderModalClose.addEventListener('click', function() {
                orderModal.classList.add('hidden');
            });
            const favoriteButtons = document.querySelectorAll('.favorite-toggle');
            // 監聽本地取消收藏同步
            const syncFavoriteRemoved = () => {
                const removedId = window.localStorage.getItem('favorite-removed');
                if (removedId) {
                    favoriteButtons.forEach(function (button) {
                        if (button.getAttribute('data-request-list-id') === removedId) {
                            button.classList.remove('text-pink-500', 'bg-pink-50');
                            button.classList.add('text-gray-400', 'bg-gray-100');
                            button.setAttribute('aria-pressed', 'false');
                        }
                    });
                    window.localStorage.removeItem('favorite-removed');
                }
            };
            syncFavoriteRemoved();
            window.addEventListener('focus', syncFavoriteRemoved);

            favoriteButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const requestListId = button.getAttribute('data-request-list-id');
                    fetch("{{ route('favorite.toggle') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ type: 'request_list', id: requestListId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const isActive = button.classList.contains('text-pink-500');
                        if (data.status === 'added') {
                            button.classList.add('text-pink-500', 'bg-pink-50');
                            button.classList.remove('text-gray-400', 'bg-gray-100');
                            button.setAttribute('aria-pressed', 'true');
                        } else if (data.status === 'removed') {
                            button.classList.remove('text-pink-500', 'bg-pink-50');
                            button.classList.add('text-gray-400', 'bg-gray-100');
                            button.setAttribute('aria-pressed', 'false');
                        }
                    });
                });
            });
        });
    </script>
</x-app-layout>