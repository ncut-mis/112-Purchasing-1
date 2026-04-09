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
                                class="inline-flex items-center rounded-lg bg-blue-500 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-600" 
                                onclick="openDetail({{ $noti->id }})">
                            檢視
                        </button>
                        <button class="flex-1 md:flex-none px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-bold shadow-sm shadow-amber-200 transition-all active:scale-95">
                            接受代購
                        </button>
                        <button class="flex-1 md:flex-none px-6 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-xl text-sm font-bold transition-all">
                            拒絕
                        </button>
                    </div>
                </div>
            </div>

            {{-- 2. 檢視內容 Modal (左右雙欄版) --}}
            <div id="request-detail-modal-{{ $noti->id }}" 
                 class="request-detail-modal hidden fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 px-4 py-4" 
                 onclick="if(event.target === this) closeDetail({{ $noti->id }})">
                
                <div class="w-full max-w-5xl overflow-hidden rounded-[2rem] bg-white shadow-2xl" onclick="event.stopPropagation()">
                    {{-- Header: 藍色漸層 --}}
                    <div class="relative flex items-start justify-between gap-4 bg-gradient-to-r from-blue-600 to-cyan-500 px-8 py-6 text-white">
                        <div>
                            <p class="text-sm font-medium text-blue-100 opacity-90">代購人已下單的請購清單</p>
                            <h4 class="mt-1 text-3xl font-bold">{{ $noti->title }}</h4>
                            <p class="mt-1 text-sm text-blue-50 opacity-80">可查看商品明細與目前接單狀況。</p>
                        </div>
                        <button type="button" class="rounded-full bg-white/20 p-2 text-white transition hover:bg-white/40" onclick="closeDetail({{ $noti->id }})">
                            <i class="bi bi-x-lg text-xl"></i>
                        </button>
                    </div>

                    {{-- Body: 雙欄佈局 --}}
                    <div class="grid grid-cols-1 gap-8 p-8 lg:grid-cols-[1.6fr_1fr] max-h-[75vh] overflow-y-auto text-left">
                        
                        {{-- 左欄 --}}
<div class="space-y-6">
    {{-- 1. 商品明細 --}}
    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm ring-1 ring-slate-200/50">
        <h5 class="text-lg font-bold text-slate-800">商品明細</h5>
        <div class="mt-4 space-y-4">
            @foreach($noti->items as $item)
            <div class="flex gap-4 rounded-2xl border border-slate-100 p-4 transition hover:bg-slate-50/50">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                    @if($item->reference_image)
                        <img src="{{ url('/request-item-image/' . $item->id) }}" class="h-full w-full object-cover rounded-xl">
                    @else
                        <span class="text-[10px] text-center px-1">無商品圖片</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h6 class="font-bold text-slate-800 truncate">{{ $item->name }}</h6>
                    <p class="mt-1 text-sm text-slate-500 font-medium">數量：{{ $item->quantity }}</p>
                    <div class="mt-2 inline-block rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600">
                        規格/時段：{{ $item->specification ?? '未提供' }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- 2. 商家資訊（上） --}}
    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm ring-1 ring-slate-200/50 text-sm">
        <h5 class="font-bold text-slate-800 text-base mb-4">商家與購買資訊</h5>
        <div class="space-y-3">
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
                <span class="text-slate-500 font-medium">國家</span>
                <span class="font-bold text-slate-800">{{ $countries[$noti->country] ?? $noti->country ?? '未提供' }}</span>
            </div>
            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
                <span class="text-slate-500 font-medium">店家名稱</span>
                <span class="font-bold text-slate-800">{{ $noti->title }}</span>
            </div>
            <div class="pt-2">
                <span class="text-slate-500 block mb-2 font-medium">詳細地址</span>
                <div class="rounded-xl bg-slate-50 p-3 text-slate-700 text-xs leading-relaxed">
                    {{ $noti->detail_address ?? '未提供詳細地址' }}
                </div>
            </div>
        </div>
    </section>

    {{-- 3. 商品單價（下） --}}
    <section class="rounded-3xl border border-blue-100 bg-blue-50/20 p-6 shadow-sm ring-1 ring-blue-50">
        <h5 class="text-sm font-bold text-blue-600 mb-4 uppercase tracking-wider">代購人填寫的商品單價</h5>
        <div class="grid grid-cols-1 gap-3">
            @foreach($noti->items as $item)
            <div class="flex items-center justify-between gap-4 p-4 rounded-xl bg-white border border-blue-100/50 shadow-sm">
                {{-- 商品名稱：放大到 text-base 並移除縮寫限制 --}}
                <span class="text-base font-bold text-slate-800 flex-1 leading-snug whitespace-normal">
                    {{ $item->name }}
                </span>
                
                <div class="text-right shrink-0 border-l border-slate-100 pl-4">
                    {{-- 幣別也稍微大一點 --}}
                    <span class="text-xs font-black text-slate-400 block uppercase">{{ $noti->currency }}</span>
                    {{-- 金額放大到 text-xl --}}
                    <span class="text-xl font-black text-blue-700">
                        {{ number_format((float) ($item->expected_price ?? 0), 0) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </section>
</div>

                        {{-- 右欄 --}}
                        <div class="space-y-6">
                            <section class="rounded-3xl border border-blue-50 bg-blue-50/30 p-8 text-center ring-1 ring-blue-100">
                                <h5 class="text-base font-bold text-slate-800 mb-6 text-left">目前接單狀況</h5>
                                <div class="mx-auto h-24 w-24 rounded-full border-4 border-white bg-blue-600 shadow-md flex items-center justify-center overflow-hidden">
                                     @if($noti->agent && $noti->agent->avatar)
                                        <img src="{{ asset('storage/' . $noti->agent->avatar) }}" class="h-full w-full object-cover">
                                     @else
                                        <span class="text-3xl font-black text-white">{{ mb_substr($noti->agent->name ?? '代', 0, 1) }}</span>
                                     @endif
                                </div>
                                <h6 class="mt-4 text-xl font-black text-slate-800">{{ $noti->agent->name ?? '代購人' }}</h6>
                                <p class="mt-2 text-sm text-slate-500 px-4">此代購人已下單並提供商品單價</p>
                            </section>

                            
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