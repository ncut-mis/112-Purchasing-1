{{-- resources/views/dashboard/partials/_notifications.blade.php --}}
<div class="max-w-7xl mx-auto px-6 py-8">
    <!-- Header -->
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
                    <p class="opacity-90 text-lg">{{ $notifications->total() }} 筆請購動態</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-8 py-5 text-left text-sm font-bold text-gray-900 uppercase tracking-wider w-24">狀態</th>
                        <th class="px-8 py-5 text-left text-sm font-bold text-gray-900 uppercase tracking-wider">商品名稱</th>
                        <th class="px-8 py-5 text-left text-sm font-bold text-gray-900 uppercase tracking-wider">狀態</th>
                        <th class="px-8 py-5 text-left text-sm font-bold text-gray-900 uppercase tracking-wider">更新時間</th>
                        <th class="px-8 py-5 text-right text-sm font-bold text-gray-900 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($notifications as $item)
                        <tr class="hover:bg-indigo-50 transition-all hover:shadow-sm">
                            <td class="px-8 py-6">
                                @if($item->created_at >= now()->subDay())
                                    <span class="inline-flex px-3 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full">🆕 新增</span>
                                @elseif($item->updated_at >= now()->subHour())
                                    <span class="inline-flex px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">✨ 更新</span>
                                @else
                                    <span class="inline-flex px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-full">📋 歷史</span>
                                @endif
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-semibold text-lg text-gray-900">{{ $item->title ?? '未命名項目' }}</div>
                                <div class="text-sm text-gray-500">ID: #{{ $item->id }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">
                                    {{ $item->status ?? '處理中' }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-sm text-gray-600">
                               {{ \Carbon\Carbon::parse($item->updated_at)->format('Y-m-d') }}<br>
                                <span class="text-xs opacity-75">{{ \Carbon\Carbon::parse($item->updated_at)->diffForHumans() }}</span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <a href="#" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white text-sm font-bold rounded-2xl hover:from-indigo-700 hover:to-blue-700 transition-all shadow-lg hover:shadow-xl hover:-translate-y-px">
                                    檢視詳情
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-24 text-center">
                                <svg class="w-24 h-24 mx-auto mb-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">沒有新通知</h3>
                                <p class="text-gray-600 max-w-sm mx-auto">最近7天內新增或更新的請購項目會自動顯示</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-12 flex justify-center">
        {{ $notifications->appends(request()->query())->links() }}
    </div>
</div>