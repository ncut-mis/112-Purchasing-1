<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-indigo-800 leading-tight">
                {{ __('代購接單大廳') }}
            </h2>
            
            <div class="flex items-center gap-3">
                <!-- 搜尋功能：讓代購人快速鎖定關鍵字 -->
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="pl-10 pr-4 py-2 border-gray-200 rounded-full text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm w-64" placeholder="搜尋商品名稱或關鍵字...">
                </div>

                <!-- 返回請購人主頁面 -->
                <a href="{{ route('home') }}" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-full text-sm font-medium text-gray-600 hover:bg-gray-50 transition shadow-sm">
                    <i class="bi bi-arrow-left-right text-indigo-500"></i>
                    <span>返回買家模式</span>
                </a>

                <!-- 會員專區按鈕 (從 Dashboard 進入管理核心) -->
                <a href="{{ route('agent.member') }}" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent rounded-full text-sm font-bold text-white hover:bg-indigo-700 transition shadow-md">
                    <i class="bi bi-person-badge"></i>
                    <span>會員專區</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- 篩選器區域 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8 space-y-6">
                <!-- 篩選國家按鈕 -->
                <div>
                    <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="bi bi-geo-alt"></i> 依國家篩選
                    </h5>
                    <div class="flex flex-wrap gap-2">
                        <button class="px-5 py-2 rounded-full bg-indigo-600 text-white font-bold text-sm shadow-md">全部地區</button>
                        <button class="px-5 py-2 rounded-full bg-gray-50 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition text-sm font-medium">🇯🇵 日本</button>
                        <button class="px-5 py-2 rounded-full bg-gray-50 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition text-sm font-medium">🇰🇷 韓國</button>
                        <button class="px-5 py-2 rounded-full bg-gray-50 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition text-sm font-medium">🇹🇭 泰國</button>
                        <button class="px-5 py-2 rounded-full bg-gray-50 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition text-sm font-medium">🇺🇸 美國</button>
                        <button class="px-5 py-2 rounded-full bg-gray-50 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition text-sm font-medium">🇪🇺 歐洲</button>
                        <button class="px-5 py-2 rounded-full bg-gray-50 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition text-sm font-medium">🇦🇺 澳洲</button>
                    </div>
                </div>

                <!-- 篩選截止時間按鈕 -->
                <div class="pt-6 border-t border-gray-50">
                    <h5 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="bi bi-clock-history"></i> 依截止時間
                    </h5>
                    <div class="flex flex-wrap gap-2">
                        <button class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:border-indigo-500 hover:text-indigo-600 transition text-xs font-bold">不限時間</button>
                        <button class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:border-red-500 hover:text-red-600 transition text-xs font-bold flex items-center gap-2">
                            <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span> 最緊急 (24H內)
                        </button>
                        <button class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:border-orange-500 hover:text-orange-600 transition text-xs font-bold">3 天內截止</button>
                        <button class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:border-indigo-500 hover:text-indigo-600 transition text-xs font-bold">本週截止</button>
                    </div>
                </div>
            </div>

            <!-- 主內容：請購清單 (許願池) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-bold text-gray-800">最新請購需求</h3>
                    <span class="text-sm text-gray-400">找到 128 個符合條件的許願</span>
                </div>

                <!-- 請購清單 Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- 清單項目 1 -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:border-indigo-200 hover:shadow-md transition group">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-inner">
                                    <i class="bi bi-bag-heart-fill text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800 group-hover:text-indigo-600 transition">日本限定吉伊卡哇睡衣</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md font-bold">🇯🇵 日本代購</span>
                                        <span class="text-[10px] px-2 py-0.5 bg-red-50 text-red-600 rounded-md font-bold">剩餘 8 小時</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-black text-indigo-600">$1,500</div>
                                <div class="text-[10px] text-gray-400 font-medium">預估預算</div>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-500 mb-6 leading-relaxed line-clamp-2">
                            希望可以幫忙在澀谷或是表參道的專賣店買到這款限定睡衣，尺寸需要 M 號，如果是當場連線的話可以再追加其他的周邊商品...
                        </p>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                            <div class="flex items-center gap-2">
                                <img src="https://ui-avatars.com/api/?name=User&background=f3f4f6" class="w-6 h-6 rounded-full">
                                <span class="text-xs text-gray-500">請購人：小甜甜</span>
                            </div>
                            <button class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 hover:scale-105 transition transform shadow-lg shadow-indigo-100 flex items-center gap-2">
                                <i class="bi bi-cart-plus"></i> 我要接單
                            </button>
                        </div>
                    </div>

                    <!-- 清單項目 2 -->
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 hover:border-indigo-200 hover:shadow-md transition group">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-inner">
                                    <i class="bi bi-lightning-fill text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800 group-hover:text-indigo-600 transition">韓國 Mardi Mercredi 經典短 T</h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md font-bold">🇰🇷 韓國代購</span>
                                        <span class="text-[10px] px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md font-bold">剩餘 3 天</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-black text-indigo-600">$2,400</div>
                                <div class="text-[10px] text-gray-400 font-medium">預估預算</div>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-500 mb-6 leading-relaxed line-clamp-2">
                            想要經典的小雛菊圖案綠色款，希望最近在韓國連線的代購人可以幫忙帶回，不趕時間，能寄店到店即可...
                        </p>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                            <div class="flex items-center gap-2">
                                <img src="https://ui-avatars.com/api/?name=Agent&background=f3f4f6" class="w-6 h-6 rounded-full">
                                <span class="text-xs text-gray-500">請購人：王先生</span>
                            </div>
                            <button class="px-8 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-sm hover:bg-indigo-700 hover:scale-105 transition transform shadow-lg shadow-indigo-100 flex items-center gap-2">
                                <i class="bi bi-cart-plus"></i> 我要接單
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>