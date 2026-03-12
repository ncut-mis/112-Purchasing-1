<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('申請成為代購人') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            <!-- 返回按鈕 -->
            <div class="mb-6">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 flex items-center gap-2 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>返回</span>
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- 頂部裝飾條 -->
                <div class="h-2 bg-indigo-600"></div>
                
                <div class="p-8">
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-gray-800">代購人身分認證</h3>
                        <p class="text-gray-500 mt-2">請提供真實資訊以利審核，我們將嚴格保護您的隱私資料。</p>
                    </div>

                    <!-- 成功/錯誤訊息 -->
                    @if (session('success'))
                        <div class="mb-6 p-4 bg-green-50 text-green-700 border border-green-200 rounded-xl flex items-center gap-3">
                            <i class="bi bi-check-circle-fill"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('agent.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- 基本資料區塊 -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- 姓名 -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">真實姓名</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    class="w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                    placeholder="請輸入身分證姓名">
                                @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- 出生地國家 -->
                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700 mb-1">出生地國家</label>
                                <select name="country" id="country" required
                                    class="w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                    <option value="">請選擇您的出生地</option>
                                    <option value="台灣">台灣</option>
                                    <option value="中國">中國</option>
                                    <option value="香港">香港</option>
                                    <option value="澳門">澳門</option>
                                    <option value="日本">日本</option>
                                    <option value="韓國">韓國</option>
                                    <option value="馬來西亞">馬來西亞</option>
                                    <option value="新加坡">新加坡</option>
                                    <option value="美國">美國</option>
                                </select>
                                @error('country') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- 電話 -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">聯絡電話</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                                    class="w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                    placeholder="0912-345-678">
                                @error('phone') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <!-- 身分證字號 -->
                            <div>
                                <label for="id_number" class="block text-sm font-medium text-gray-700 mb-1">身分證字號</label>
                                <input type="text" name="id_number" id="id_number" value="{{ old('id_number') }}" required
                                    class="w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                    placeholder="例如：A123456789">
                                @error('id_number') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- 證件上傳區塊 -->
                        <div class="space-y-4 pt-4 border-t border-gray-50">
                            <h4 class="font-bold text-gray-700">身分證件上傳</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- 正面 -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-2">證件正面照</label>
                                    <div class="relative group">
                                        <div id="preview-front" class="w-full h-48 rounded-2xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center bg-gray-50 overflow-hidden transition group-hover:bg-gray-100">
                                            <i class="bi bi-camera text-3xl text-gray-300 mb-2"></i>
                                            <span class="text-xs text-gray-400">點擊上傳正面</span>
                                        </div>
                                        <input type="file" name="id_image_front" class="absolute inset-0 opacity-0 cursor-pointer" 
                                            onchange="previewImage(this, 'preview-front')" accept="image/*" required>
                                    </div>
                                    @error('id_image_front') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>

                                <!-- 反面 -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-2">證件反面照</label>
                                    <div class="relative group">
                                        <div id="preview-back" class="w-full h-48 rounded-2xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center bg-gray-50 overflow-hidden transition group-hover:bg-gray-100">
                                            <i class="bi bi-camera text-3xl text-gray-300 mb-2"></i>
                                            <span class="text-xs text-gray-400">點擊上傳反面</span>
                                        </div>
                                        <input type="file" name="id_image_back" class="absolute inset-0 opacity-0 cursor-pointer" 
                                            onchange="previewImage(this, 'preview-back')" accept="image/*" required>
                                    </div>
                                    @error('id_image_back') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 送出按鈕 -->
                        <div class="pt-6">
                            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                                <i class="bi bi-send-fill"></i>
                                提交申請
                            </button>
                            <p class="text-center text-xs text-gray-400 mt-4 italic">
                                * 提交申請即表示您同意本站代購人服務條款
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 圖片預覽腳本 -->
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                    preview.classList.remove('border-dashed');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-app-layout>