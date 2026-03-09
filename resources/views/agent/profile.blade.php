<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-indigo-800 leading-tight">
            {{ __('編輯代購人資訊') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <a href="{{ route('agent.member') }}" class="text-sm text-gray-500 hover:text-indigo-600 flex items-center gap-2 transition">
                    <i class="bi bi-arrow-left"></i> 返回會員專區
                </a>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <form action="{{ route('agent.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- 頭像設定：移除小圓點，改為點擊頭像區域 -->
                        <div class="flex flex-col items-center mb-10">
                            <div class="relative group">
                                <!-- Label 包裹整個預覽區塊，點擊任何地方都會觸發 input -->
                                <label for="avatar-input" class="relative block cursor-pointer">
                                    <div id="avatar-preview" class="w-32 h-32 rounded-full border-4 border-white shadow-md overflow-hidden bg-indigo-500 flex items-center justify-center transition group-hover:brightness-90">
                                        @if(Auth::user()->avatar)
                                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-full h-full object-cover">
                                        @else
                                            <!-- 如果沒有頭像，顯示預設 UI Avatars 或文字 -->
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=6366f1&color=fff&size=128" class="w-full h-full object-cover">
                                        @endif

                                        <!-- 懸停時的半透明遮罩與編輯圖示 -->
                                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-300">
                                            <i class="bi bi-camera-fill text-white text-2xl"></i>
                                        </div>
                                    </div>
                                </label>
                                
                                <!-- 隱藏的檔案輸入框 -->
                                <input type="file" id="avatar-input" name="avatar" class="hidden" onchange="previewAvatar(this)" accept="image/*">
                            </div>
                            <p class="text-xs text-gray-400 mt-4 font-medium">點擊頭像更換圖片</p>
                            <p class="text-[10px] text-gray-300 mt-1">建議尺寸 400x400 (JPG/PNG)</p>
                        </div>

                        <div class="space-y-6">
                            <!-- 暱稱設定 -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">顯示暱稱</label>
                                <input type="text" name="nickname" value="{{ Auth::user()->name }}" 
                                    class="w-full border-gray-100 bg-gray-50 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 transition px-4 py-3"
                                    placeholder="輸入您想在名片上顯示的名稱">
                            </div>

                            <!-- 個人簡介 -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">個人簡介 (Slogan)</label>
                                <textarea name="bio" rows="3" 
                                    class="w-full border-gray-100 bg-gray-50 rounded-2xl focus:ring-indigo-500 focus:border-indigo-500 transition px-4 py-3"
                                    placeholder="簡單介紹您的代購特色，例如：日本藥妝直送，每週採買...">{{ Auth::user()->bio }}</textarea>
                                <p class="text-[10px] text-gray-400 mt-1">這段文字會顯示在您的個人名片下方。</p>
                            </div>
                        </div>
                            <!-- 可代購國家 (新增區塊) -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-3">可代購國家 (複選)</label>
                                <div class="flex flex-wrap gap-3">
                                    @php
                                        // 從資料庫解析 JSON
                                        $selectedCountries = json_decode(Auth::user()->purchasable_countries ?? '[]', true) ?: [];
                                    @endphp
                                    
                                    <!-- 日本選項 -->
                                    <label class="relative cursor-pointer">
                                        <input type="checkbox" name="countries[]" value="日本" class="peer hidden" {{ in_array('日本', $selectedCountries) ? 'checked' : '' }}>
                                        <div class="px-6 py-2 rounded-xl border border-gray-200 text-sm font-bold text-gray-500 peer-checked:bg-indigo-50 peer-checked:border-indigo-500 peer-checked:text-indigo-600 transition">
                                            🇯🇵 日本
                                        </div>
                                    </label>

                                    <!-- 韓國選項 -->
                                    <label class="relative cursor-pointer">
                                        <input type="checkbox" name="countries[]" value="韓國" class="peer hidden" {{ in_array('韓國', $selectedCountries) ? 'checked' : '' }}>
                                        <div class="px-6 py-2 rounded-xl border border-gray-200 text-sm font-bold text-gray-500 peer-checked:bg-indigo-50 peer-checked:border-indigo-500 peer-checked:text-indigo-600 transition">
                                            🇰🇷 韓國
                                        </div>
                                    </label>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-2 italic">* 選取的國家將會以標籤形式顯示在您的名片上。</p>
                            </div>
                        <div class="mt-10">
                            <button type="submit" class="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition transform hover:scale-[1.02]">
                                儲存個人資訊
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
                    // 尋找預覽區塊中的圖片並替換
                    const previewDiv = document.getElementById('avatar-preview');
                    let img = previewDiv.querySelector('img');
                    
                    if (!img) {
                        img = document.createElement('img');
                        img.className = 'w-full h-full object-cover';
                        previewDiv.prepend(img);
                    }
                    
                    img.src = e.target.result;
                    // 移除背景色與文字（如果有的話）
                    previewDiv.classList.remove('bg-indigo-500');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-app-layout>