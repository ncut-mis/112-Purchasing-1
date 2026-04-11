<x-app-layout>


<div class="p-6 max-w-7xl mx-auto">
    <div class="flex justify-between items-start mb-8">
        
        <div class="flex items-start">
            
            <a href="{{ url('/agent/member') }}" 
               class="shrink-0 inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm"
               style="width: auto; min-width: 80px;">
                <i class="bi bi-chevron-left text-xs"></i>
                <span class="text-sm font-bold leading-none">返回</span>
            </a>
            
            <div class="ml-6">
                <h1 class="text-2xl font-bold text-gray-800">物流設定</h1>
                <p class="text-sm text-gray-500 mt-1">管理您的配送方式、付款選項及配送時段</p>
            </div>
        </div>

        <button onclick="openModal('add')" 
            class="shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold transition shadow-lg shadow-indigo-100 flex items-center gap-2">
            <i class="bi bi-plus-lg"></i> 新增物流
        </button>
    </div>

<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">物流名稱</th>
                        <th class="px-6 py-4 font-semibold">付款方式</th>
                        <th class="px-6 py-4 font-semibold">運送溫層</th>
                        <th class="px-6 py-4 font-semibold">可配送時段</th>
                        <th class="px-6 py-4 font-semibold">狀態</th>
                        <th class="px-6 py-4 font-semibold text-right">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($logistics as $item)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-700">{{ $item->name }}</div>
                            <div class="text-xs text-gray-400">{{ $item->ship_type }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->payment_method }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-blue-50 text-blue-600 text-xs font-medium rounded-md">
                                {{ $item->temp_layer ?? '未設定' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1 max-w-[180px]">
                                @php
                                    $times = is_array($item->available_times) ? $item->available_times : json_decode($item->available_times, true);
                                @endphp
                                @forelse($times ?? [] as $time)
                                    <span class="px-1.5 py-0.5 bg-gray-100 text-gray-500 text-[10px] font-bold rounded">{{ $time }}</span>
                                @empty
                                    <span class="text-gray-300 text-xs">未設定</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="flex items-center gap-1.5 {{ $item->status ? 'text-green-600' : 'text-gray-400' }} text-sm font-bold">
                                <span class="w-2 h-2 {{ $item->status ? 'bg-green-500' : 'bg-gray-300' }} rounded-full"></span>
                                {{ $item->status ? '已啟用' : '停用中' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick='openModal("edit", @json($item))' 
                                class="text-indigo-600 hover:text-indigo-900 font-bold text-sm bg-indigo-50 px-3 py-1.5 rounded-lg transition">
                                編輯
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">目前尚無物流設定</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

<div id="logisticsModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 id="modalTitle" class="text-lg font-bold text-gray-800">新增物流</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="logisticsForm" action="{{ route('logistics.save') }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="id" id="field_id">

            <div class="space-y-5">
                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                    <label class="block text-sm font-bold text-gray-700 mb-2">國內物流名稱</label>
                    <input type="text" name="name" id="field_name" required
                        class="w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-2.5" 
                        placeholder="例如：黑貓宅急便">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 border border-gray-100 rounded-2xl">
                        <label class="block text-sm font-bold text-gray-700 mb-3">啟用狀態</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="status" value="1" id="status_active" checked class="text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-600">啟用</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="status" value="0" id="status_inactive" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-600">停用</span>
                            </label>
                        </div>
                    </div>

                    <div class="p-4 border border-gray-100 rounded-2xl">
                        <label class="block text-sm font-bold text-gray-700 mb-3">出貨方式</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="ship_type" value="宅配" id="ship_home" class="text-indigo-600">
                                <span class="ml-2 text-sm text-gray-600">宅配</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="ship_type" value="超商" id="ship_store" class="text-indigo-600">
                                <span class="ml-2 text-sm text-gray-600">超商</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">付款方式</label>
                        <select name="payment" id="field_payment" class="w-full rounded-xl border-gray-200">
                            <option value="線上付款">線上付款</option>
                            <option value="貨到付款">貨到付款</option>
                            <option value="其他">其他</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">運送溫層</label>
                        <select name="temp_layer" id="field_temp" class="w-full rounded-xl border-gray-200">
                            <option value="常溫">常溫</option>
                            <option value="冷藏">冷藏</option>
                            <option value="冷凍">冷凍</option>
                        </select>
                    </div>
                </div>

                <div class="p-4 border border-gray-100 rounded-2xl">
                    <label class="block text-sm font-bold text-gray-700 mb-3">可配送時段</label>
                    <div class="grid grid-cols-4 gap-y-3">
                        @foreach(['週一', '週二', '週三', '週四', '週五', '週六', '週日', '全週'] as $day)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="times[]" value="{{ $day }}" 
                                class="rounded text-indigo-600 checkbox-time"
                                @if($day === '全週') id="check-all-week" @endif> <span class="ml-2 text-xs text-gray-600">{{ $day }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="button" onclick="closeModal()" 
                    class="flex-1 py-3 bg-gray-100 text-gray-600 rounded-xl font-bold hover:bg-gray-200 transition">取消</button>
                <button type="submit" 
                    class="flex-1 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition">儲存設定</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(mode, data = null) {
        const modal = document.getElementById('logisticsModal');
        const form = document.getElementById('logisticsForm');
        const title = document.getElementById('modalTitle');
        
        // 重置表單
        form.reset();
        document.getElementById('field_id').value = '';
        
        if (mode === 'edit' && data) {
            title.innerText = '編輯物流設定';
            document.getElementById('field_id').value = data.id;
            document.getElementById('field_name').value = data.name;
            document.getElementById('field_payment').value = data.payment_method;
            document.getElementById('field_temp').value = data.temp_layer;
            
            // 設定 Radio
            if(data.status == 1) document.getElementById('status_active').checked = true;
            else document.getElementById('status_inactive').checked = true;
            
            if(data.ship_type === '宅配') document.getElementById('ship_home').checked = true;
            else if(data.ship_type === '超商') document.getElementById('ship_store').checked = true;

            // 設定 Checkbox
            const times = data.available_times || [];
            document.querySelectorAll('.checkbox-time').forEach(cb => {
                if(times.includes(cb.value)) cb.checked = true;
            });
        } else {    
            title.innerText = '新增國內物流';
        }

        // --- 關鍵新增：在這裡呼叫檢查功能 ---
        // 這樣不管是點「編輯」帶入資料，還是點「新增」重置表單，都會重新判斷一次全週的鎖定狀態
        setTimeout(checkAllWeekStatus, 10);
        // ------------------------------

        modal.classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('logisticsModal').classList.add('hidden');
    }

    // 監聽手動點擊 Checkbox 的行為
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('checkbox-time')) {
            checkAllWeekStatus(); // 只要有變動就跑一次檢查
        }
    });

    // 定義檢查函式
    function checkAllWeekStatus() {
        const allWeekBtn = document.getElementById('check-all-week');
        const otherCheckboxes = document.querySelectorAll('.checkbox-time:not(#check-all-week)');
        
        if (allWeekBtn && allWeekBtn.checked) {
            // 選中全週時：取消其他勾選，並禁用
            otherCheckboxes.forEach(cb => {
                cb.checked = false;
                cb.disabled = true;
                cb.parentElement.classList.add('opacity-50', 'cursor-not-allowed');
            });
        } else if (allWeekBtn) {
            // 取消全週時：解除禁用
            otherCheckboxes.forEach(cb => {
                cb.disabled = false;
                cb.parentElement.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        }
    }
</script>
</x-app-layout>