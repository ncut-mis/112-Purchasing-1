@extends('layouts.front')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="fw-bold mb-0">建立請託單</h2>
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4">返回首頁</a>
                        </div>

                        <form action="{{ route('request-list.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-semibold">請託單標題</label>
                                <input type="text" class="form-control" name="title" placeholder="請輸入請託單標題" value="{{ old('title') }}" required>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-semibold mb-0">商品清單（最多 3 筆）</h5>
                                <button type="button" class="btn btn-outline-success" id="add-item-btn">
                                    <i class="bi bi-plus-lg me-1"></i>新增商品
                                </button>
                            </div>
                            <p class="text-muted small mb-3" id="item-limit-hint">可再新增 2 筆商品。</p>

                            <style>
                                .image-preview-wrapper {
                                    display: flex;
                                    gap: 8px;
                                    align-items: flex-end;
                                }
                                .image-preview {
                                    width: 100px;
                                    height: 100px;
                                    background-color: #e9ecef;
                                    border: 2px solid #dee2e6;
                                    border-radius: 8px;
                                    display: block;
                                    overflow: hidden;
                                    object-fit: contain;
                                    padding: 4px;
                                    box-sizing: border-box;
                                    flex-shrink: 0;
                                }
                                .image-preview.has-image {
                                    background-color: #fff;
                                    border: 2px solid #dee2e6;
                                }
                            </style>

                            <div id="item-list">
                                <div class="row g-3 align-items-end mb-3 item-row" data-index="0">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">商品名稱</label>
                                        <input type="text" class="form-control" name="items[0][item_name]" placeholder="請輸入商品名稱">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">數量</label>
                                        <input type="number" class="form-control" name="items[0][quantity]" min="1" value="1">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">商品圖片</label>
                                        <input type="file" class="form-control item-image-input" name="items[0][item_image]" accept="image/*">
                                    </div>
                                    <div class="col-md-3">
                                        <div class="image-preview-wrapper">
                                            <button type="button" class="btn btn-outline-danger remove-item-btn" disabled>刪除</button>
                                            <img class="image-preview" src="" alt="預覽">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                               <div class="col-md-6">
                                    <label class="form-label fw-semibold">選擇國家</label>
                                    <select class="form-select" name="country" required>
                                        <option value="">請選擇國家</option>
                                        <option value="jp">日本</option>
                                        <option value="kr">韓國</option>
                                        <option value="us">美國</option>
                                        <option value="gb">英國</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">商品截止日</label>
                                    <input
                                        type="date"
                                        class="form-control"
                                        name="deadline"
                                        min="{{ now()->toDateString() }}"
                                        max="{{ now()->addMonth()->toDateString() }}"
                                        required
                                    >
                                    <div class="form-text">截止日僅能設定為建立當天起1個月內。</div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">店家</label>
                                    <input type="text" class="form-control" name="store_name" placeholder="請輸入店家名稱" value="{{ old('store_name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">詳細地址</label>
                                    <input type="text" class="form-control" name="detail_address" placeholder="請輸入詳細地址" value="{{ old('detail_address') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">備註</label>
                                    <textarea class="form-control" name="note" rows="3" placeholder="可填寫請購需求補充、注意事項等">{{ old('note') }}</textarea>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-success px-5">確認</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const maxItems = 3;
        const itemList = document.getElementById('item-list');
        const addItemBtn = document.getElementById('add-item-btn');
        const itemLimitHint = document.getElementById('item-limit-hint');

        addItemBtn.addEventListener('click', function () {
            const count = itemList.querySelectorAll('.item-row').length;
            if (count >= maxItems) {
                return;
            }

            const index = count;
            const row = document.createElement('div');
            row.className = 'row g-3 align-items-end mb-3 item-row';
            row.setAttribute('data-index', index);
            row.innerHTML = `
                <div class="col-md-3">
                    <label class="form-label fw-semibold">商品名稱</label>
                    <input type="text" class="form-control" name="items[${index}][item_name]" placeholder="請輸入商品名稱" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">數量</label>
                    <input type="number" class="form-control" name="items[${index}][quantity]" min="1" value="1" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">商品圖片</label>
                    <input type="file" class="form-control item-image-input" name="items[${index}][item_image]" accept="image/*">
                </div>
                <div class="col-md-3">
                    <div class="image-preview-wrapper">
                        <button type="button" class="btn btn-outline-danger remove-item-btn">刪除</button>
                        <img class="image-preview" src="" alt="預覽">
                    </div>
                </div>
            `;
            itemList.appendChild(row);
            
            // 為新的圖片輸入框添加預覽監聽
            const newImageInput = row.querySelector('.item-image-input');
            newImageInput.addEventListener('change', handleImagePreview);
            
            updateUiState();
        });

        itemList.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-item-btn')) {
                event.target.closest('.item-row').remove();
                updateIndexes();
                updateUiState();
            }
        });

        // 圖片預覽函數
        function handleImagePreview(event) {
            const file = event.target.files[0];
            const preview = event.target.closest('.item-row').querySelector('.image-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.classList.remove('has-image');
            }
        }

        // 為所有現有的圖片輸入框綁定預覽事件
        itemList.querySelectorAll('.item-image-input').forEach(input => {
            input.addEventListener('change', handleImagePreview);
        });

        function updateIndexes() {
            itemList.querySelectorAll('.item-row').forEach((row, index) => {
                row.setAttribute('data-index', index);
                row.querySelector('input[name*="[item_name]"]').setAttribute('name', `items[${index}][item_name]`);
                row.querySelector('input[name*="[quantity]"]').setAttribute('name', `items[${index}][quantity]`);
                row.querySelector('input[name*="[item_image]"]').setAttribute('name', `items[${index}][item_image]`);
            });
        }

        function updateUiState() {
            const rows = itemList.querySelectorAll('.item-row');
            const remaining = maxItems - rows.length;

            rows.forEach((row) => {
                const removeBtn = row.querySelector('.remove-item-btn');
                removeBtn.disabled = rows.length === 1;
            });

            addItemBtn.disabled = rows.length >= maxItems;
            itemLimitHint.textContent = remaining > 0
                ? `可再新增 ${remaining} 筆商品。`
                : '已達商品上限（最多 3 筆）。';
        }

        updateUiState();
    });
</script>
@endsection
