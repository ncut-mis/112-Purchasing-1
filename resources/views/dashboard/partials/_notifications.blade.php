{{-- resources/views/dashboard/partials/_notifications.blade.php --}}
@dd($offers)
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="p-8 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-3xl shadow-2xl mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">通知中心</h1>
                    <p class="opacity-90 text-lg">目前共有 {{ $offers->count() }} 筆代購報價</p>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        @forelse($offers as $offer)
            @php
                $noti = $offer->requestList; // 取得關聯的需求清單
                $agent = $offer->user;        // 取得報價的代購人
            @endphp

            <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-all">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex items-start gap-5">
                        <div class="relative">
                            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-500 border border-indigo-100">
                                <i class="bi bi-person-badge text-3xl"></i>
                            </div>
                            <span class="absolute -bottom-2 -right-2 px-2 py-0.5 bg-emerald-500 text-white text-[10px] font-bold rounded-lg shadow-sm uppercase">PRO</span>
                        </div>
                        
                        <div>
                            <div class="flex items-center gap-3">
                                <h4 class="text-xl font-bold text-gray-800">{{ $agent->name }} 的報價</h4>
                                <span class="text-xs text-gray-400 font-medium">更新於 {{ $offer->updated_at->diffForHumans() }}</span>
                            </div>
                            
                            <p class="text-gray-500 mt-1">
                                針對項目：<span class="font-bold text-indigo-600">「{{ $noti->title }}」</span>
                            </p>
                            
                            <div class="mt-4 flex flex-wrap gap-3">
                                <div class="px-4 py-2 bg-slate-50 rounded-2xl border border-slate-100 flex items-center gap-2">
                                    <span class="text-xs text-slate-400 font-bold uppercase">總金額</span>
                                    <span class="font-black text-slate-900">{{ $offer->currency }} {{ number_format($offer->agent_quote_total) }}</span>
                                </div>
                                <div class="px-4 py-2 bg-slate-50 rounded-2xl border border-slate-100 flex items-center gap-2">
                                    <span class="text-xs text-slate-400 font-bold uppercase">預計交期</span>
                                    <span class="font-bold text-slate-900">{{ $offer->time ?? '待議' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 w-full md:w-auto">
                        <button type="button" 
                                class="flex-1 md:flex-none px-6 py-3 bg-white border-2 border-indigo-100 text-indigo-600 rounded-2xl text-sm font-bold hover:bg-indigo-50 transition shadow-sm"
                                onclick="openRequestChatModal({{ $offer->id }})">
                            <i class="bi bi-chat-dots-fill mr-2"></i> 與他聊聊
                        </button>

                        <form action="{{ route('quotes.accept', $offer->id) }}" method="POST" class="flex-1 md:flex-none">
                            @csrf
                            <button type="submit" onclick="return confirm('接受此報價將會建立訂單，確定嗎？')"
                                    class="w-full px-8 py-3 bg-indigo-600 text-white rounded-2xl text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition">
                                接受報價
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-32 text-center bg-white rounded-3xl border border-dashed border-gray-200">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-bell-slash text-3xl text-gray-300"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800">目前沒有收到報價</h3>
                <p class="text-gray-500 mt-2">當代購人對您的需求出價時，會顯示在這裡。</p>
            </div>
        @endforelse
    </div>
</div>