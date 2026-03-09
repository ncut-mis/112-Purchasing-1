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

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- 數據統計區 -->
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
                <!-- 左側：代購管理導航 -->
                <div class="w-full lg:w-1/4 space-y-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 bg-indigo-600 text-white font-bold flex items-center justify-between">
                            <span>管理工具箱</span>
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <nav class="p-2 space-y-1">
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <i class="bi bi-receipt text-indigo-500"></i>
                                <span>訂單管理</span>
                            </a>
                            <a href="#connections" class="flex items-center gap-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <i class="bi bi-megaphone text-orange-500"></i>
                                <span>我的代購連線</span>
                            </a>
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <i class="bi bi-box-seam text-blue-500"></i>
                                <span>代購商品管理</span>
                            </a>
                            <a href="#payments" class="flex items-center gap-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <i class="bi bi-wallet2 text-emerald-500"></i>
                                <span>撥款紀錄</span>
                            </a>
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <i class="bi bi-bookmark-heart text-pink-500"></i>
                                <span>收藏請購清單</span>
                            </a>
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <i class="bi bi-chat-heart text-yellow-500"></i>
                                <span>評價中心</span>
                            </a>
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                                <i class="bi bi-truck text-indigo-500"></i>
                                <span>物流設定</span>
                            </a>

                            <div class="border-t border-gray-50 my-2 pt-2"></div>
                            
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl text-indigo-600 font-bold hover:bg-indigo-50 transition">
                                <i class="bi bi-plus-circle-fill"></i>
                                <span>發布新的連線</span>
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

                <!-- 右側主管理區 -->
                <div class="w-full lg:w-3/4 space-y-8">
                    <!-- 我的代購連線 -->
                    <section id="connections" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-800">我的活躍連線</h3>
                            <button class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-indigo-700 transition">+ 發布連線</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 border border-gray-100 rounded-2xl flex gap-4 hover:border-indigo-200 transition">
                                <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center text-gray-300">
                                    <i class="bi bi-image text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h6 class="font-bold text-gray-800 text-sm">【日本】吉伊卡哇三月代購</h6>
                                    <p class="text-[10px] text-gray-400">發布於: 2025-02-20</p>
                                    <div class="mt-2 flex gap-2">
                                        <span class="text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded">12 人跟單</span>
                                        <span class="text-[10px] text-green-600 font-bold bg-green-50 px-2 py-0.5 rounded">進行中</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- 撥款紀錄 -->
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
</x-app-layout>