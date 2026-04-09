<div class="bg-white rounded-2xl shadow-sm p-6">
    <div>
        <h3 class="text-lg font-bold text-gray-800">通知中心</h3>
        <p class="mt-1 text-sm text-gray-500">這裡會顯示帶購人對您的請購單所提出的報價，您可以選擇接受或拒絕。</p>
    </div>
    @php
            // 在循環外定義一次即可，效能較好且代碼整潔
            $countries = [
                'kr' => '韓國', 'jp' => '日本', 
                'us' => '美國', 'gb' => '英國', 
            ];
    @endphp

    <div class="space-y-4 mt-6">
        @forelse($offeredRequests as $noti)
            {{-- 1. 通知卡片本體 --}}
            <div class="group relative rounded-2xl border border-gray-100 bg-white p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center text-amber-500 border border-amber-100">
                            <i class="bi bi-person-check-fill text-xl"></i>
                        </div>
                        
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-gray-800">有人想幫您代購！</h4>
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full uppercase tracking-wider">待處理報價</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                代購人：<span class="font-medium text-gray-700">{{ $noti->agent->name ?? '尚未指派' }}</span>
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <div class="flex items-center gap-1.5 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100 text-xs text-gray-600">
                                    <i class="bi bi-currency-dollar text-amber-600"></i>
                                    報價總額：<span class="font-bold text-gray-900">{{ $noti->currency }} {{ number_format($noti->agent_quote_total) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100 text-xs text-gray-600">
                                    <i class="bi bi-clock text-blue-500"></i>
                                    預計時間：<span class="font-bold text-gray-900">{{ $noti->time ?? '未提供時間' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 w-full md:w-auto">
                        <button type="button" 
                                class="inline-flex items-center rounded-lg bg-yellow-500 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-yellow-600" 
                                onclick="openDetail({{ $noti->id }})">
                            詳細內容
                        </button>
                        <button class="flex-1 md:flex-none px-6 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-xl text-sm font-bold shadow-sm shadow-green-200 transition-all active:scale-95">
                            接受代購
                        </button>
                        <button class="flex-1 md:flex-none px-6 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-xl text-sm font-bold shadow-sm shadow-gray-200 transition-all active:scale-95">
                            拒絕
                        </button>
                    </div>
                </div>
            </div>

            {{-- 2. 檢視內容 Modal (左右雙欄版) --}}
            <div id="request-detail-modal-{{ $noti->id }}" 
                 class="request-detail-modal hidden fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 px-4 py-4" 
                 onclick="if(event.target === this) closeDetail({{ $noti->id }})">
                
                 <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl" onclick="event.stopPropagation()">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 bg-gradient-to-r from-amber-600 to-yellow-500 px-5 py-4 text-white">
                        <div>
                            <p class="text-sm font-medium text-amber-100">詳細內容</p>
                            <h4 class="mt-1 text-2xl font-bold">{{ $noti->title }}</h4>
                            <p class="text-sm font-medium text-amber-100">以下是詳細商品明細,請確認詳細內容再決定是否接受此代購人幫您代購</p>
                        </div>
                         <button type="button" class="rounded-full bg-white/15 p-2 text-white transition hover:bg-white/30" onclick="closeDetail({{ $noti->id }})" aria-label="關閉檢視視窗">✕</button>
                    </div>

                    <div class="max-h-[72vh] overflow-y-auto px-5 py-5 text-left">
                       <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500">國家:{{ $countries[$noti->country] ?? $noti->country ?? '未提供' }}</dt>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500">商家:{{ $noti->title ?: '未提供' }}</dt>  
                            </div>
                            <div class="space-y-1">
                                <dt class="text-slate-500">商家詳細地址：{{ $noti->detail_address ?: '未提供詳細地址' }}</dt>
                            </div>
                        </dl>

                        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-center">
                            <div class="mx-auto flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-4 border-blue-100 bg-slate-100">
                                @if($noti->agent && $noti->agent->avatar)
                                    <img src="{{ asset('storage/' . $noti->agent->avatar) }}" alt="{{ $noti->agent->name }}" class="h-full w-full object-cover">
                                @else
                                    <span class="text-2xl font-black text-slate-700">{{ mb_substr($noti->agent->name ?? '代', 0, 1) }}</span>
                                @endif
                            </div>
                            <p class="mt-3 text-base font-semibold text-slate-800">{{ $noti->agent->name ?? '代購人' }}</p>
                            <p class="mt-1 text-sm text-slate-600">此代購人已下單以下是他所提供的商品單價</p>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                        <th class="px-2 py-2 font-medium">商品圖片</th>
                                        <th class="px-2 py-2 font-medium">商品名稱</th>
                                        <th class="px-2 py-2 font-medium">需求量</th>
                                        <th class="px-2 py-2 font-medium">代購人填寫之單價</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($noti->items as $item)
                                        <tr class="border-b border-slate-100">
                                            <td class="px-2 py-3">
                                                <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-slate-400">
                                                    @if($item->reference_image)
                                                        <img src="{{ url('/request-item-image/' . $item->id) }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                                    @else
                                                        <span class="text-[10px]">無圖片</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-2 py-3 font-medium text-slate-800">{{ $item->name }}</td>
                                            <td class="px-2 py-3 text-slate-700">{{ $item->quantity }}</td>
                                            <td class="px-2 py-3 text-slate-800">
                                                @if(!is_null($item->expected_price))
                                                NT$ {{ number_format((float) $item->expected_price, 0) }}
                                                @else
                                                 未提供
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-2 py-6 text-center text-slate-400">目前沒有商品資料。</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/50 px-6 py-16 text-center">
                <i class="bi bi-bell-slash text-2xl text-gray-300"></i>
                <p class="text-gray-400 font-medium mt-4">目前尚無帶購人報價。</p>
            </div>
        @endforelse
    </div>

    <script>
        window.openDetail = function(id) {
            const modal = document.getElementById('request-detail-modal-' + id);
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeDetail = function(id) {
            const modal = document.getElementById('request-detail-modal-' + id);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        };
    </script>
</div>