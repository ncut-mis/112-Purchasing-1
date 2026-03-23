<x-app-layout>
    <!-- 背景與頭像區 -->
    <div class="relative bg-emerald-800 pb-32 pt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2 text-emerald-100 mb-8">
                <a href="{{ route('store') }}" class="hover:underline flex items-center gap-1">
                    <i class="bi bi-arrow-left"></i> 返回找代購
                </a>
            </div>
            
            <div class="flex flex-col md:flex-row items-center md:items-end gap-6">
                <!-- 頭像 -->
                <div class="relative -mb-16 md:-mb-24">
                    <img src="{{ $agent->avatar ? asset('storage/' . $agent->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($agent->name) . '&background=10b981&color=fff&size=200' }}" 
                         class="w-40 h-40 md:w-56 md:h-56 rounded-full border-8 border-white shadow-xl object-cover bg-white">
                    <div class="absolute bottom-4 right-4 w-8 h-8 bg-green-500 border-4 border-white rounded-full"></div>
                </div>
                
                <!-- 暱稱與基本資訊 -->
                <div class="text-center md:text-left md:pb-6 flex-1">
                    <h1 class="text-3xl md:text-5xl font-black text-white mb-2">{{ $agent->nickname ?? $agent->name }}</h1>
                    <div class="flex flex-wrap justify-center md:justify-start gap-3">
                        <span class="px-4 py-1 bg-white/20 backdrop-blur-md text-white rounded-full text-xs font-bold border border-white/30 uppercase tracking-widest">
                            <i class="bi bi-shield-check me-1"></i> 已認證代購
                        </span>
                        <span class="px-4 py-1 bg-emerald-600 text-white rounded-full text-xs font-bold border border-emerald-500">
                            {{ $agent->agentApplication->country ?? '台灣' }} 駐點
                        </span>
                    </div>
                </div>

                <!-- 互動按鈕 -->
                <div class="md:pb-6 flex gap-3">
                    <button class="bg-white text-emerald-800 px-8 py-3 rounded-2xl font-black shadow-lg hover:bg-emerald-50 transition flex items-center gap-2">
                        <i class="bi bi-chat-fill"></i> 聊一聊
                    </button>
                    <button class="bg-white/10 hover:bg-white/20 text-white p-3 rounded-2xl border border-white/20 transition">
                        <i class="bi bi-share"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 內容區域 -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 md:pt-32 pb-20">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- 左側：簡介與資訊 -->
            <div class="space-y-8">
                <section class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="bi bi-card-text text-emerald-500"></i> 個人簡介
                    </h3>
                    <p class="text-gray-600 leading-relaxed italic">
                        "{{ $agent->bio ?? '這位代購人很神秘，還沒有填寫個人簡介。' }}"
                    </p>
                </section>

                <section class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="bi bi-globe text-emerald-500"></i> 服務地區
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $countries = json_decode($agent->purchasable_countries ?? '[]', true) ?: [];
                        @endphp
                        @forelse($countries as $country)
                            <span class="px-4 py-2 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-xl">
                                {{ $country }}
                            </span>
                        @empty
                            <span class="text-gray-400 text-sm">全球代購中</span>
                        @endforelse
                    </div>
                </section>
            </div>

            <!-- 右側：代購貼文 (agent_posts) -->
            <div class="lg:col-span-2 space-y-6">
                <h3 class="text-2xl font-black text-gray-800 flex items-center gap-3 mb-6">
                    <i class="bi bi-megaphone text-emerald-500"></i> 已發布代購貼文
                </h3>

                @forelse($agent->agentPosts as $post)
                    <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-gray-100 hover:border-emerald-200 transition group">
                        <div class="flex flex-col md:flex-row">
                            <!-- 貼文封面 -->
                            <div class="w-full md:w-48 h-48 bg-gray-100 flex-shrink-0 relative">
                                @if($post->cover_image)
                                    <img src="{{ asset('storage/' . $post->cover_image) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                        <i class="bi bi-image text-4xl"></i>
                                    </div>
                                @endif
                                <div class="absolute top-3 left-3 px-3 py-1 bg-black/50 backdrop-blur-sm text-white text-[10px] font-bold rounded-lg">
                                    {{ $post->country }}
                                </div>
                            </div>
                            
                            <!-- 貼文內容 -->
                            <div class="p-6 flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="text-lg font-bold text-gray-800 group-hover:text-emerald-600 transition">{{ $post->title }}</h4>
                                        <span class="text-emerald-600 font-bold text-sm bg-emerald-50 px-2 py-1 rounded-md">進行中</span>
                                    </div>
                                    <p class="text-gray-500 text-sm line-clamp-2 mb-4">
                                        {{ $post->description }}
                                    </p>
                                </div>
                                
                                <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                                    <div class="flex gap-4 text-xs text-gray-400 font-medium">
                                        <span><i class="bi bi-calendar-event me-1"></i> {{ $post->start_date }} ~ {{ $post->end_date }}</span>
                                        @if($post->city)
                                            <span><i class="bi bi-geo-alt me-1"></i> {{ $post->city }}</span>
                                        @endif
                                    </div>
                                    <a href="#" class="text-emerald-600 font-black text-sm hover:underline">查看詳情 <i class="bi bi-chevron-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-gray-50 rounded-3xl p-12 text-center border-2 border-dashed border-gray-200">
                        <i class="bi bi-inbox text-4xl text-gray-300 mb-4 block"></i>
                        <p class="text-gray-400 font-medium">目前暫無發布中的代購貼文</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>