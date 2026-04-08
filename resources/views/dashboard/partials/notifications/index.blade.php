    <div class="bg-white rounded-2xl shadow-sm p-6">
        <div>
            <h3 class="text-lg font-bold text-gray-800">通知中心</h3>
            <p class="mt-1 text-sm text-gray-500">這裡會顯示帶購人對您的請購單所提出的報價，您可以選擇接受或拒絕。</p>
        </div>

        <div class="space-y-4 mt-6">
            {{-- 這裡假設你後端傳入的變數叫 $offeredRequests，篩選 status 為 offered 的資料 --}}
            @forelse($offeredRequests as $noti)
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
                                        代購人：
                                        <span class="font-medium text-gray-700">
                                            {{ $noti->agent->name ?? '尚未指派' }}
                                        </span>
                                    </p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <div class="flex items-center gap-1.5 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100 text-xs text-gray-600">
                                        <i class="bi bi-currency-dollar text-amber-600"></i>
                                        報價總額：
                                        <span class="font-bold text-gray-900">
                                            {{-- 1. 顯示資料庫存的幣別 (例如 TWD, JPY) --}}
                                            {{ $noti->currency }} 
                                            
                                            {{-- 2. 顯示金額，並移除原本前面的 $ 符號 --}}
                                            {{ number_format($noti->agent_quote_total) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100 text-xs text-gray-600">
                                        <i class="bi bi-clock text-blue-500"></i>
                                        {{-- ★ 直接抓取你填寫的 time 欄位 ★ --}}
                                        預計時間：<span class="font-bold text-gray-900">{{ $noti->time ?? '未提供時間' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2 w-full md:w-auto">
                            <button class="flex-1 md:flex-none px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-bold shadow-sm shadow-amber-200 transition-all active:scale-95">
                                接受代購
                            </button>
                            <button class="flex-1 md:flex-none px-6 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 rounded-xl text-sm font-bold transition-all">
                                拒絕
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/50 px-6 py-16 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-sm mb-4">
                        <i class="bi bi-bell-slash text-2xl text-gray-300"></i>
                    </div>
                    <p class="text-gray-400 font-medium">目前尚無帶購人報價，請耐心等候吧！</p>
                </div>
            @endforelse
        </div>
    </div>
