<x-app-layout>
    @php
        $oldProducts = old('products', [
            ['name' => '', 'price' => '', 'max_quantity' => ''],
        ]);

        if (!is_array($oldProducts) || count($oldProducts) === 0) {
            $oldProducts = [['name' => '', 'price' => '', 'max_quantity' => '']];
        }

        $oldProducts = array_slice($oldProducts, 0, 5);
    @endphp

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-indigo-800 leading-tight">
                建立代購貼文
            </h2>

            <a href="{{ route('agent.member') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-full text-sm font-medium text-indigo-600 hover:bg-indigo-50 transition shadow-sm">
                <i class="bi bi-arrow-left"></i>
                返回代購人會員專區
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('agent.posts.store') }}" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 space-y-8">
                @csrf

                <div class="border-b border-gray-100 pb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-1">貼文資訊</h3>
                    <p class="text-sm text-gray-500">設定銷售期間與描述,並建立商品規格(最多5項)。</p>
                </div>

                @if ($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">貼文標題</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="w-full rounded-xl border-2 border-blue-500 focus:border-blue-500 focus:ring-blue-500" placeholder="例如：東京五月連線代購" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">代購國家</label>
                        <select name="country" class="w-full rounded-xl border-2 border-blue-500 focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="">請選擇</option>
                            @foreach (['日本', '韓國', '美國', '英國'] as $country)
                                <option value="{{ $country }}" @selected(old('country') === $country)>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">銷售開始日</label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full rounded-xl border-2 border-blue-500 focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">銷售結束日</label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full rounded-xl border-2 border-blue-500 focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">描述訊息</label>
                    <textarea name="description" rows="4" class="w-full rounded-xl border-2 border-blue-500 focus:border-blue-500 focus:ring-blue-500" placeholder="請描述代購規則、注意事項、收單方式..." required>{{ old('description') }}</textarea>
                </div>

                <div class="border-t border-gray-100 pt-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-800">商品規格</h3>
                        <button type="button" id="add-product-btn" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-50 text-indigo-700 font-semibold hover:bg-indigo-100 transition">
                            <i class="bi bi-plus-circle"></i>
                            新增商品
                        </button>
                    </div>

                    <div id="products-container" data-max-items="5" data-current-items="{{ count($oldProducts) }}" class="space-y-4">
                        @foreach($oldProducts as $index => $product)
                            <div class="product-item rounded-2xl border border-gray-200 p-4 md:p-5 bg-gray-50" data-index="{{ $index }}">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-semibold text-gray-700">商品 #{{ $index + 1 }}</h4>
                                    <button type="button" class="remove-product-btn text-sm text-rose-600 hover:text-rose-700 font-semibold" @if(count($oldProducts) === 1) style="display:none;" @endif>刪除</button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">商品名稱</label>
                                        <input type="text" name="products[{{ $index }}][name]" value="{{ $product['name'] ?? '' }}" class="w-full rounded-xl border-2 border-blue-500 focus:border-blue-500 focus:ring-blue-500" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">單價（TWD）</label>
                                        <input type="number" min="0" step="0.01" name="products[{{ $index }}][price]" value="{{ $product['price'] ?? '' }}" class="w-full rounded-xl border-2 border-blue-500 focus:border-blue-500 focus:ring-blue-500" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">最高數量</label>
                                        <input type="number" min="1" step="1" name="products[{{ $index }}][max_quantity]" value="{{ $product['max_quantity'] ?? '' }}" class="w-full rounded-xl border-2 border-blue-500 focus:border-blue-500 focus:ring-blue-500" required>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">商品圖片（選擇檔案）</label>
                                    <input type="file" accept="image/*" name="products[{{ $index }}][image]" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-white file:text-indigo-700 file:font-semibold hover:file:bg-indigo-50">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-sm">
                        <i class="bi bi-send-check"></i>
                        儲存並發布
                    </button>
                </div>
            </form>
        </div>
    </div>

    <template id="product-item-template">
        <div class="product-item rounded-2xl border border-gray-200 p-4 md:p-5 bg-gray-50" data-index="__INDEX__">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-semibold text-gray-700">商品 #__NUMBER__</h4>
                <button type="button" class="remove-product-btn text-sm text-rose-600 hover:text-rose-700 font-semibold">刪除</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">商品名稱</label>
                    <input type="text" name="products[__INDEX__][name]" class="w-full rounded-xl border-2 border-blue-500 focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">單價（TWD）</label>
                    <input type="number" min="0" step="0.01" name="products[__INDEX__][price]" class="w-full rounded-xl border-2 border-blue-500 focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">最高數量</label>
                    <input type="number" min="1" step="1" name="products[__INDEX__][max_quantity]" class="w-full rounded-xl border-2 border-blue-500 focus:border-blue-500 focus:ring-blue-500" required>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">商品圖片（選擇檔案）</label>
                <input type="file" accept="image/*" name="products[__INDEX__][image]" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-white file:text-indigo-700 file:font-semibold hover:file:bg-indigo-50">
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('products-container');
            const addBtn = document.getElementById('add-product-btn');
            const template = document.getElementById('product-item-template').innerHTML;
            const maxItems = parseInt(container.dataset.maxItems, 10) || 5;

            let nextIndex = Array.from(container.querySelectorAll('.product-item')).reduce((max, item) => {
                const index = parseInt(item.dataset.index, 10);
                return Number.isNaN(index) ? max : Math.max(max, index);
            }, -1) + 1;

            function updateControls() {
                const items = container.querySelectorAll('.product-item');
                const canDelete = items.length > 1;
                addBtn.disabled = items.length >= maxItems;
                addBtn.classList.toggle('opacity-50', addBtn.disabled);
                addBtn.classList.toggle('cursor-not-allowed', addBtn.disabled);

                items.forEach((item, i) => {
                    const title = item.querySelector('h4');
                    if (title) title.textContent = `商品 #${i + 1}`;
                    const removeBtn = item.querySelector('.remove-product-btn');
                    if (removeBtn) removeBtn.style.display = canDelete ? '' : 'none';
                });
            }

            container.addEventListener('click', function (event) {
                const button = event.target.closest('.remove-product-btn');
                if (!button) return;

                const item = button.closest('.product-item');
                if (!item) return;

                if (container.querySelectorAll('.product-item').length <= 1) return;
                item.remove();
                updateControls();
            });

            addBtn.addEventListener('click', function () {
                const currentCount = container.querySelectorAll('.product-item').length;
                if (currentCount >= maxItems) return;

                const html = template
                    .replaceAll('__INDEX__', String(nextIndex))
                    .replaceAll('__NUMBER__', String(currentCount + 1));

                container.insertAdjacentHTML('beforeend', html);
                nextIndex += 1;
                updateControls();
            });

            updateControls();
        });
    </script>
</x-app-layout>
