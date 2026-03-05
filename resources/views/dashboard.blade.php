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
                            <!-- 申請代購人 (Indigo 色調) -->
                            <a href="{{ route('agent.apply') }}" class="flex items-center space-x-3 p-3 rounded-lg text-indigo-600 hover:bg-indigo-50 transition border-t mt-2 pt-4">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                                <span class="font-bold">申請代購人</span>
                            </a>
                            <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 transition border-t mt-2 pt-4">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <span class="font-bold text-green-600">發布許願</span>
                            </a>
                        </nav>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-4">
                        <div class="text-sm font-bold text-gray-700 mb-3 text-center">其他功能</div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <a href="{{ route('chat.index') }}" class="p-2 hover:bg-blue-50 rounded-lg transition">
                                <i class="bi bi-chat-dots text-xl text-blue-500"></i>
                                <div class="text-[10px] mt-1">聊天</div>
                            </a>
                            <a href="#" class="p-2 hover:bg-gray-50 rounded-lg">
                                <i class="bi bi-clock-history text-xl text-gray-500"></i>
                                <div class="text-[10px] mt-1">歷史</div>
                            </a>
                            <a href="#" class="p-2 hover:bg-gray-50 rounded-lg">
                                <i class="bi bi-star text-xl text-yellow-500"></i>
                                <div class="text-[10px] mt-1">評價</div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 右側主內容區 -->
                <div class="flex-1 space-y-6">
                    
                    <!-- 請購清單區塊 (範例) -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-800">目前請購清單</h3>
                            <button class="text-sm text-green-600 hover:underline">+ 新增許願</button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="text-gray-400 text-sm border-b">
                                        <th class="pb-3 font-medium">項目名稱</th>
                                        <th class="pb-3 font-medium">地點</th>
                                        <th class="pb-3 font-medium">預算</th>
                                        <th class="pb-3 font-medium">狀態</th>
                                        <th class="pb-3 font-medium text-right">操作</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr class="text-sm">
                                        <td class="py-4 font-medium text-gray-800">日本限定 PS5 手把</td>
                                        <td class="py-4 text-gray-500">日本</td>
                                        <td class="py-4 text-gray-800">$2,400</td>
                                        <td class="py-4">
                                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-[10px]">募集中</span>
                                        </td>
                                        <td class="py-4 text-right">
                                            <button class="text-blue-500 hover:underline">編輯</button>
                                        </td>
                                    </tr>
                                    <tr class="text-sm">
                                        <td class="py-4 font-medium text-gray-800">LV 零錢包 (黑色)</td>
                                        <td class="py-4 text-gray-500">歐洲</td>
                                        <td class="py-4 text-gray-800">$18,500</td>
                                        <td class="py-4">
                                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-[10px]">已接單</span>
                                        </td>
                                        <td class="py-4 text-right">
                                            <button class="text-gray-400 cursor-not-allowed">檢視</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 聊天訊息預覽 (範例) -->
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
</x-app-layout>