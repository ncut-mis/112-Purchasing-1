@extends('layouts.front')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="fw-bold mb-0">建立請購清單</h2>
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4">返回首頁</a>
                        </div>

                        <form action="#" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-semibold mb-0">商品清單</h5>
                                <button type="button" class="btn btn-outline-success" id="add-item-btn">
                                    <i class="bi bi-plus-lg me-1"></i>新增商品
                                </button>
                            </div>

                            <div id="item-list">
                                <div class="row g-3 align-items-end mb-3 item-row" data-index="0">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">商品名稱</label>
                                        <input type="text" class="form-control" name="items[0][item_name]" placeholder="請輸入商品名稱">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">數量</label>
                                        <input type="number" class="form-control" name="items[0][quantity]" min="1" value="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">商品圖片</label>
                                        <input type="file" class="form-control" name="items[0][item_image]" accept="image/*">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-dark w-100">編輯</button>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger w-100 remove-item-btn" disabled>刪除</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">選擇國家</label>
                                    <select class="form-select" name="country">
                                        <option value="">請選擇國家</option>
                                        <option value="jp">日本</option>
                                        <option value="kr">韓國</option>
                                        <option value="us">美國</option>
                                        <option value="gb">英國</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">商品截止日</label>
                                    <input type="date" class="form-control" name="deadline">
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">店家</label>
                                    <input type="text" class="form-control" name="store_name" placeholder="請輸入店家名稱">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">詳細地址</label>
                                    <input type="text" class="form-control" name="address_detail" placeholder="請輸入詳細地址">
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
        const itemList = document.getElementById('item-list');
        const addItemBtn = document.getElementById('add-item-btn');

        addItemBtn.addEventListener('click', function () {
            const index = itemList.querySelectorAll('.item-row').length;
            const row = document.createElement('div');
            row.className = 'row g-3 align-items-end mb-3 item-row';
            row.setAttribute('data-index', index);
            row.innerHTML = `
                <div class="col-md-4">
                    <label class="form-label fw-semibold">商品名稱</label>
                    <input type="text" class="form-control" name="items[${index}][item_name]" placeholder="請輸入商品名稱">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">數量</label>
                    <input type="number" class="form-control" name="items[${index}][quantity]" min="1" value="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">商品圖片</label>
                    <input type="file" class="form-control" name="items[${index}][item_image]" accept="image/*">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-dark w-100">編輯</button>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger w-100 remove-item-btn">刪除</button>
                </div>
            `;
            itemList.appendChild(row);
            updateRemoveButtons();
        });

        itemList.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-item-btn')) {
                event.target.closest('.item-row').remove();
                updateIndexes();
                updateRemoveButtons();
            }
        });

        function updateIndexes() {
            itemList.querySelectorAll('.item-row').forEach((row, index) => {
                row.setAttribute('data-index', index);
                row.querySelector('input[name*="[item_name]"]').setAttribute('name', `items[${index}][item_name]`);
                row.querySelector('input[name*="[quantity]"]').setAttribute('name', `items[${index}][quantity]`);
                row.querySelector('input[name*="[item_image]"]').setAttribute('name', `items[${index}][item_image]`);
            });
        }

        function updateRemoveButtons() {
            const rows = itemList.querySelectorAll('.item-row');
            rows.forEach((row) => {
                const removeBtn = row.querySelector('.remove-item-btn');
                removeBtn.disabled = rows.length === 1;
            });
        }

        updateRemoveButtons();
    });
</script>
@endsection