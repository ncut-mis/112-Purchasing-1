<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-indigo-800 leading-tight">
            {{ __('編輯代購人資訊') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <a href="{{ route('agent.member') }}" class="text-sm font-bold text-gray-400 hover:text-indigo-600 flex items-center gap-2 transition">
                    <i class="bi bi-arrow-left"></i> 返回會員專區
                </a>
            </div>

            <!-- 主卡片：強化邊框顏色與陰影，使其更加明顯 -->
            <div class="bg-white rounded-[3rem] shadow-[0_20px_50px_rgba(79,70,229,0.15)] border-2 border-indigo-100 overflow-hidden">
                <div class="p-8 md:p-12">
                    <form action="{{ route('agent.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- 頭像設定區塊 -->
                        <div class="flex flex-col items-center mb-12">
                            <div class="relative group">
                                <label for="avatar-input" class="relative block cursor-pointer">
                                    <div id="avatar-preview" class="w-36 h-36 rounded-full border-4 border-white shadow-2xl overflow-hidden bg-indigo-500 flex items-center justify-center transition group-hover:scale-105 group-hover:brightness-90">
                                        @if(Auth::user()->avatar)
                                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-full h-full object-cover">
                                        @else
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=6366f1&color=fff&size=128" class="w-full h-full object-cover">
                                        @endif

                                        <div class="absolute inset-0 bg-indigo-900/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                                            <div class="text-center">
                                                <i class="bi bi-camera-fill text-white text-2xl"></i>
                                                <div class="text-[10px] text-white font-bold mt-1 uppercase tracking-widest">更換圖片</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <input type="file" id="avatar-input" name="avatar" class="hidden" onchange="previewAvatar(this)" accept="image/*">
                            </div>
                            <h4 class="mt-4 font-black text-gray-800 text-lg">{{ Auth::user()->name }}</h4>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">建議尺寸 400x400 (JPG/PNG)</p>
                        </div>

                        <div class="space-y-10">
                            <!-- 顯示暱稱 -->
                            <div>
                                <label class="block text-xs font-black text-indigo-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                                    <i class="bi bi-person-badge"></i> 顯示暱稱
                                </label>
                                <input type="text" name="nickname" value="{{ Auth::user()->nickname ?? Auth::user()->name }}" 
                                    class="w-full bg-white border-2 border-indigo-50 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all px-6 py-4 text-gray-700 font-medium shadow-sm"
                                    placeholder="輸入您想在名片上顯示的名稱">
                            </div>

                            <!-- 個人簡介 -->
                            <div>
                                <label class="block text-xs font-black text-indigo-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                                    <i class="bi bi-chat-quote"></i> 個人簡介 (Slogan)
                                </label>
                                <textarea name="bio" rows="4" 
                                    class="w-full bg-white border-2 border-indigo-50 rounded-[2rem] focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all px-6 py-4 text-gray-700 font-medium shadow-sm"
                                    placeholder="簡單介紹您的代購特色，例如：日本藥妝直送，每週採買...">{{ Auth::user()->bio }}</textarea>
                                <p class="text-[10px] text-gray-400 mt-2 font-medium italic">* 這段文字將會顯示在您的代購人名片上。</p>
                            </div>

                            <!-- 可代購國家 -->
                            <div>
                                <label class="block text-xs font-black text-indigo-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <i class="bi bi-globe-americas"></i> 可代購國家 (複選)
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    @php
                                        $countriesData = Auth::user()->purchasable_countries;
                                        if (is_array($countriesData)) {
                                            $selectedCountries = $countriesData;
                                        } else {
                                            $selectedCountries = json_decode($countriesData ?? '[]', true) ?? [];
                                            if (is_string($selectedCountries)) {
                                                $selectedCountries = json_decode($selectedCountries, true) ?? [];
                                            }
                                        }
                                        if (!is_array($selectedCountries)) $selectedCountries = [];
                                    @endphp
                                    
                                    <!-- 日本選項 -->
                                    <label class="relative cursor-pointer">
                                        <input type="checkbox" name="countries[]" value="日本" class="peer hidden" {{ in_array('日本', $selectedCountries) ? 'checked' : '' }}>
                                        <div class="px-4 py-3 rounded-2xl border-2 border-indigo-50 bg-white text-xs font-bold text-gray-400 peer-checked:bg-indigo-500 peer-checked:border-indigo-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-indigo-200 transition-all text-center">
                                            🇯🇵 日本
                                        </div>
                                    </label>

                                    <!-- 韓國選項 -->
                                    <label class="relative cursor-pointer">
                                        <input type="checkbox" name="countries[]" value="韓國" class="peer hidden" {{ in_array('韓國', $selectedCountries) ? 'checked' : '' }}>
                                        <div class="px-4 py-3 rounded-2xl border-2 border-indigo-50 bg-white text-xs font-bold text-gray-400 peer-checked:bg-indigo-500 peer-checked:border-indigo-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-indigo-200 transition-all text-center">
                                            🇰🇷 韓國
                                        </div>
                                    </label>

                                    <!-- 【新增】美國選項 -->
                                    <label class="relative cursor-pointer">
                                        <input type="checkbox" name="countries[]" value="美國" class="peer hidden" {{ in_array('美國', $selectedCountries) ? 'checked' : '' }}>
                                        <div class="px-4 py-3 rounded-2xl border-2 border-indigo-50 bg-white text-xs font-bold text-gray-400 peer-checked:bg-indigo-500 peer-checked:border-indigo-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-indigo-200 transition-all text-center">
                                            🇺🇸 美國
                                        </div>
                                    </label>

                                    <!-- 【新增】英國選項 -->
                                    <label class="relative cursor-pointer">
                                        <input type="checkbox" name="countries[]" value="英國" class="peer hidden" {{ in_array('英國', $selectedCountries) ? 'checked' : '' }}>
                                        <div class="px-4 py-3 rounded-2xl border-2 border-indigo-50 bg-white text-xs font-bold text-gray-400 peer-checked:bg-indigo-500 peer-checked:border-indigo-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-indigo-200 transition-all text-center">
                                            🇬🇧 英國
                                        </div>
                                    </label>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-4 italic">* 選取的國家將以動態標籤形式呈現給買家。</p>
                            </div>
                        </div>

                        <!-- 送出按鈕 -->
                        <div class="mt-16">
                            <button type="submit" class="w-full bg-indigo-600 text-white font-black py-5 rounded-[2rem] shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 transition-all active:scale-95 text-lg">
                                <i class="bi bi-cloud-check-fill me-2"></i> 儲存個人資訊
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewDiv = document.getElementById('avatar-preview');
                    let img = previewDiv.querySelector('img');
                    
                    if (!img) {
                        img = document.createElement('img');
                        img.className = 'w-full h-full object-cover';
                        previewDiv.prepend(img);
                    }
                    
                    img.src = e.target.result;
                    previewDiv.classList.remove('bg-indigo-500');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-app-layout>