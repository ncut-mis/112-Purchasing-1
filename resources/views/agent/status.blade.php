<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('代購申請進度') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            <!-- 返回按鈕 -->
            <div class="mb-6">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 flex items-center gap-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>返回控制台</span>
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- 狀態顏色條 -->
                @if(in_array($application->status, ['pending', 'resubmitted']))
                    <div class="h-2 bg-yellow-400"></div>
                @elseif($application->status == 'approved')
                    <div class="h-2 bg-green-500"></div>
                @else
                    <div class="h-2 bg-red-500"></div>
                @endif
                
                <div class="p-8 text-center">
                    <!-- 狀態圖示 -->
                    <div class="mb-6">
                        @if($application->status == 'pending')
                            <div class="w-20 h-20 bg-yellow-50 text-yellow-500 rounded-full flex items-center justify-center mx-auto text-4xl shadow-sm">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mt-4">申請審核中</h3>
                            <p class="text-gray-500 mt-2">您的資料已成功提交，管理員正在努力審核中，請耐心等候。</p>
                        @elseif($application->status == 'resubmitted')
                            <div class="w-20 h-20 bg-yellow-50 text-yellow-500 rounded-full flex items-center justify-center mx-auto text-4xl shadow-sm">
                                <i class="bi bi-arrow-repeat"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mt-4">重新申請中</h3>
                            <p class="text-gray-500 mt-2">已收到您重新提交的資料，目前由管理員重新審核中。</p>
                        @elseif($application->status == 'approved')
                            <div class="w-20 h-20 bg-green-50 text-green-500 rounded-full flex items-center justify-center mx-auto text-4xl shadow-sm">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mt-4">恭喜！申請已通過</h3>
                            <p class="text-gray-500 mt-2">您現在已經具備代購人身分，可以開始發布代購貼文了。</p>
                            <div class="mt-6">
                                <a href="#" class="btn btn-success rounded-pill px-6 fw-bold">前往發布貼文</a>
                            </div>
                        @else
                            <div class="w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto text-4xl shadow-sm">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mt-4">申請未通過</h3>
                            <p class="text-gray-500 mt-2">很抱歉，您的申請未能通過審核。</p>
                            @if($application->admin_remark)
                                <div class="mt-4 p-4 bg-red-50 text-red-700 rounded-xl text-sm italic">
                                    原因：{{ $application->admin_remark }}
                                </div>
                            @endif
                            <div class="mt-6">
                                <a href="{{ route('agent.apply', ['resubmit' => 1]) }}" class="text-indigo-600 font-bold hover:underline">重新提交申請</a>
                            </div>
                        @endif
                    </div>

                    <!-- 申請資訊摘要 -->
                    <div class="mt-10 border-t border-gray-50 pt-8 text-left">
                        <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">您的申請資訊</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-xl">
                                <span class="text-xs text-gray-400 block">真實姓名</span>
                                <span class="font-medium text-gray-700">{{ $application->name }}</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-xl">
                                <span class="text-xs text-gray-400 block">出生地國家</span>
                                <span class="font-medium text-gray-700">{{ $application->country }}</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-xl">
                                <span class="text-xs text-gray-400 block">聯絡電話</span>
                                <span class="font-medium text-gray-700">{{ $application->phone }}</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-xl">
                                <span class="text-xs text-gray-400 block">提交時間</span>
                                <span class="font-medium text-gray-700">{{ $application->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <p class="text-center text-xs text-gray-400 mt-8">
                如有任何疑問，請聯繫 <a href="mailto:support@globalbuy.com" class="hover:underline">客服中心</a>
            </p>
        </div>
    </div>
</x-app-layout>