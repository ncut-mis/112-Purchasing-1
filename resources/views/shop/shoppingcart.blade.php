{{-- resources/views/shop/shoppingcart.blade.php --}}
@extends('layouts.furni')


@section('content')

<div class="untree_co-section">
    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-10">
                <div id="cart-selection-info" class="alert alert-warning d-none sticky-top" style="top: 80px; z-index: 1020;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <span>一次只能勾選同一位賣家的商品進行結帳。</span>
                </div>

                {{-- ========================= --}}
                {{-- A 區塊：專屬代購跟單項目 --}}
                {{-- ========================= --}}
                @if($followOrders->isNotEmpty())

                    <div class="mb-5">

                        <h4 class="text-info fw-bold mb-3">
                            <i class="bi bi-file-earmark-text-fill"></i>
                            專屬代購跟單項目
                        </h4>

                        @foreach($followOrders as $order)

                           <div class="seller-group card mb-3"
     data-seller-id="seller_{{ $order->seller->id ?? 'system' }}">

    <div class="card-header d-flex justify-content-between align-items-center">

        {{-- 左邊：checkbox + 代購人 --}}
        <div class="d-flex align-items-center">

            <input type="checkbox"
                   class="item-selector me-3"
                   data-seller-id="seller_{{ $order->seller->id ?? 'system' }}"
                   data-title="{{ $order->items->pluck('name')->join('、') }}"
                   data-price="{{ $order->total_amount }}"
                   data-type="follow"
                   value="{{ $order->id }}">

            <span class="small text-muted fw-bold">
                代購人：{{ $order->seller->name ?? '系統匹配' }}
            </span>

        </div>

        {{-- 右邊：移除按鈕 --}}
        <form action="{{ route('order.cancel', $order->id) }}"
              method="POST"
              onsubmit="return confirm('確定要移除這筆代購訂單與裡面的所有商品嗎？')">

            @csrf
            @method('DELETE')

            <button type="submit"
                    class="btn btn-link text-danger btn-sm text-decoration-none">

                <i class="bi bi-trash3-fill me-1"></i>
                移除整筆訂單

            </button>

        </form>

    </div>
                                

                                {{-- 商品表格 --}}
                                <div class="card-body p-0">

                                    <div class="table-responsive">

                                        <table class="table table-hover align-middle mb-0">

                                            <thead class="table-light small">

                                                <tr>

                                                    <th class="ps-3" style="width:40%">
                                                        商品名稱
                                                    </th>

                                                    <th class="text-center" style="width:15%">
                                                        單價
                                                    </th>

                                                    <th class="text-center" style="width:25%">
                                                        數量
                                                    </th>

                                                    <th class="text-end pe-3" style="width:20%">
                                                        小計
                                                    </th>

                                                </tr>

                                            </thead>

                                            <tbody>

                                                @foreach($order->items as $item)

                                                    <tr>

                                                        {{-- 商品名稱 --}}
                                                        <td class="ps-3 seller-title">

                                                            <div class="fw-bold text-dark">
                                                                {{ $item->name }}
                                                            </div>

                                                        </td>

                                                        {{-- 單價 --}}
                                                        <td class="text-center text-muted">

                                                            NT$
                                                            {{ number_format($item->price, 0) }}

                                                        </td>

                                                        {{-- 數量 --}}
                                                        <td class="text-center">

                                                            <div class="d-flex justify-content-center align-items-center">

                                                                {{-- 減號 --}}
                                                                <form action="{{ route('cart.update', $item->id) }}"
                                                                      method="POST"
                                                                      class="d-inline decrease-form">

                                                                    @csrf
                                                                    @method('PATCH')

                                                                    <input type="hidden"
                                                                           name="quantity"
                                                                           value="{{ $item->quantity - 1 }}">

                                                                    <button type="submit"
                                                                            class="btn btn-sm btn-outline-secondary px-2 py-1 decrease-btn"
                                                                            data-next-quantity="{{ $item->quantity - 1 }}"
                                                                            data-item-name="{{ $item->name }}">

                                                                        <i class="bi bi-dash-lg small"></i>

                                                                    </button>

                                                                </form>

                                                                {{-- 數字框 --}}
                                                                <form action="{{ route('cart.update', $item->id) }}"
                                                                      method="POST"
                                                                      class="d-inline mx-2 quantity-form">

                                                                    @csrf
                                                                    @method('PATCH')

                                                                    @php
                                                                        $maxAdjustable = $item->product
                                                                            ? (
                                                                                ($item->product->max_quantity ?? 0)
                                                                                - ($item->product->sold_quantity ?? 0)
                                                                                + $item->quantity
                                                                              )
                                                                            : null;
                                                                    @endphp

                                                                    <input type="number"
                                                                           name="quantity"
                                                                           value="{{ $item->quantity }}"
                                                                           class="form-control form-control-sm text-center fw-bold quantity-input"
                                                                           style="width:60px;"
                                                                           min="0"
                                                                           data-item-name="{{ $item->name }}"
                                                                           @if(!is_null($maxAdjustable))
                                                                               max="{{ max(0, $maxAdjustable) }}"
                                                                           @endif>

                                                                </form>

                                                                {{-- 加號 --}}
                                                                <form action="{{ route('cart.update', $item->id) }}"
                                                                      method="POST"
                                                                      class="d-inline">

                                                                    @csrf
                                                                    @method('PATCH')

                                                                    @php
                                                                        $isPlusDisabled =
                                                                            !is_null($maxAdjustable)
                                                                            && $item->quantity >= $maxAdjustable;
                                                                    @endphp

                                                                    <input type="hidden"
                                                                           name="quantity"
                                                                           value="{{ $item->quantity + 1 }}">

                                                                    <button type="submit"
                                                                            class="btn btn-sm btn-outline-secondary px-2 py-1"
                                                                            {{ $isPlusDisabled ? 'disabled' : '' }}>

                                                                        <i class="bi bi-plus-lg small"></i>

                                                                    </button>

                                                                </form>

                                                            </div>

                                                        </td>

                                                        {{-- 小計 --}}
                                                        <td class="text-end pe-3 fw-bold text-success item-price" data-price="{{ $item->subtotal }}">

                                                            NT$
                                                            {{ number_format($item->subtotal, 0) }}

                                                        </td>

                                                    </tr>
                                            
                                                @endforeach

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                                {{-- Footer --}}
                                <div class="card-footer bg-white text-end py-3 pe-3">

                                    <span class="text-muted me-2">

                                        共
                                        {{ $order->items->sum('quantity') }}
                                        件商品，總計：

                                    </span>

                                    <h4 class="d-inline text-danger fw-bold m-0">

                                        NT$
                                        {{ number_format($order->total_amount, 0) }}

                                    </h4>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @endif

                {{-- ========================= --}}
                {{-- 移除商品確認 Modal --}}
                {{-- ========================= --}}
                <div class="modal fade"
                     id="removeItemConfirmModal"
                     tabindex="-1"
                     aria-labelledby="removeItemConfirmModalLabel"
                     aria-hidden="true">

                    <div class="modal-dialog modal-dialog-centered">

                        <div class="modal-content">

                            <div class="modal-header">

                                <h5 class="modal-title"
                                    id="removeItemConfirmModalLabel">

                                    移除商品確認

                                </h5>

                                <button type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal"
                                        aria-label="Close">
                                </button>

                            </div>

                            <div class="modal-body">

                                確定要將
                                「<span id="removeItemName"
                                        class="fw-bold text-danger"></span>」
                                從跟單項目移除嗎？

                            </div>

                            <div class="modal-footer">

                                <button type="button"
                                        class="btn btn-secondary"
                                        data-bs-dismiss="modal">

                                    取消

                                </button>

                                <button type="button"
                                        class="btn btn-danger"
                                        id="confirmRemoveItemBtn">

                                    確認移除

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- ========================= --}}
                {{-- B 區塊：專屬代購報價單 --}}
                {{-- ========================= --}}
                    @if($requestLists->isNotEmpty())
                        
                        <div class="mb-5">
                            
                            <h4 class="text-info fw-bold mb-3">

                                <i class="bi bi-file-earmark-text-fill me-2"></i>
                                專屬代購報價單

                            </h4>

                            @foreach($requestLists as $list)
    {{-- 最外層 row 同時擔任 seller-group --}}
    <div class="row mb-3 p-3 bg-white shadow-sm border-start border-5 border-info rounded align-items-center seller-group" 
         data-seller-id="seller_{{ $list->agent->id ?? 'system' }}">
        
        <div class="col-1">
        <input type="checkbox"
            class="item-selector"
            data-seller-id="seller_{{ $list->agent->id ?? 'system' }}"
            data-title="{{ $list->title }}"
            data-price="{{ $list->agent_quote_total }}"
            data-type="request"
            value="{{ $list->id }}">
        </div>
        
        <div class="col-md-6">
            <h5 class="mb-1 text-dark">{{ $list->title }}</h5>
            <p class="mb-0 small text-muted">
                代購人：{{ $list->agent->name ?? '系統匹配' }}
            </p>
        </div>

        {{-- 這一整塊現在正確地包在 row 裡面 --}}
        <div class="col-md-5 text-end">
            <h5 class="text-success fw-bold item-price" data-price="{{ $list->agent_quote_total }}">
                NT$ {{ number_format($list->agent_quote_total, 0) }}
            </h5>
        </div>
    </div>
                            @endforeach

                        </div>

                    @endif

                {{-- ========================= --}}
                {{-- 空購物車 --}}
                {{-- ========================= --}}
                @if($followOrders->isEmpty() && $requestLists->isEmpty())

                    <div class="text-center py-5">

                        <h3>您的結帳區是空的</h3>

                        <a href="/dashboard"
                           class="btn btn-primary mt-3">

                            回個人專區

                        </a>

                    </div>

                @else

                    {{-- ========================= --}}
                    {{-- 結帳資訊 --}}
                    {{-- ========================= --}}
                    <div class="row mt-5 border-top pt-4">
    <div class="col-md-12 text-end mt-4">

    <p class="mb-1">
        商品小計：
        <span class="fw-bold">
            NT$ <span id="display_subtotal">0</span>
        </span>
    </p>

    <p class="mb-2">
        預計運費：
        <span class="fw-bold">
            NT$ 60
        </span>
    </p>

    <h3 class="text-black mb-3">
        結帳總計：
        <strong class="text-success h2">
            NT$ <span id="display_total">0</span>
        </strong>
    </h3>

    <button
        type="button"
        class="btn btn-dark w-100 py-3"
        onclick="prepareCheckout()">

        立即前往支付

    </button>

</div>

                @endif

            </div>
        </div>
       
      
    </div>
</div>

{{-- ========================= --}}
{{-- 結帳 Modal --}}

<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center">結帳</h5>
                <button type="button" class="btn-clo
                se" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="checkoutForm" action="{{ route('checkout.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="selected_follow_orders" id="selected_follow_orders">
                    <input type="hidden" name="selected_request_lists" id="selected_request_lists">
                    <input type="hidden" name="seller_id" id="selected_seller_id">

                    <h6>確認購買清單</h6>
                    {{-- 這裡只放容器，JS 會自動填入內容 --}}
                    <div id="checkout_items_list" class="border p-3 mb-3"></div>

                    <h6>總計金額 (含運費)</h6>
                    <h5 class="text-danger mb-3">NT$ <span id="modal_total_amount">0</span></h5>
                    
                    {{-- 付款、地址、物流區塊保持不變 --}}
                    <h6>付款方式</h6>
                    <div class="d-flex justify-content-between mb-3">
                        <input type="radio" class="btn-check" name="payment_method" id="pay1" value="linepay" checked>
                        <label class="btn btn-outline-secondary flex-grow-1 mx-1" for="pay1">LINE Pay</label>
                        <input type="radio" class="btn-check" name="payment_method" id="pay2" value="bank">
                        <label class="btn btn-outline-secondary flex-grow-1 mx-1" for="pay2">超商付款</label>
                        <input type="radio" class="btn-check" name="payment_method" id="pay3" value="jkopay">
                        <label class="btn btn-outline-secondary flex-grow-1 mx-1" for="pay3">街口支付</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">送貨地址：</label>
                        <input type="text" name="address" class="form-control" required placeholder="請輸入收件地址">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">物流：</label>
                        <select name="logistics_id" class="form-select">
                            @foreach($logistics as $item)
                                <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->temp_layer }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-secondary px-5">確定付款</button>
                        <button type="button" class="btn btn-light border px-5" data-bs-dismiss="modal">取消</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ========================= --}}
{{-- JS --}}
{{-- ========================= --}}
{{-- 1. CSS 必須寫在 script 標籤外面 --}}
<style>
    .is-locked {
        opacity: 0.5 !important;
    
    }
</style>

{{-- 2. JS 區塊 --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    // 更新鎖定與金額
    function updateLockState() {

        const checkedBoxes =
            document.querySelectorAll('.item-selector:checked');

        const activeSellerId =
            checkedBoxes.length > 0
                ? checkedBoxes[0].dataset.sellerId
                : null;

        let subtotal = 0;

        document.querySelectorAll('.seller-group').forEach(group => {

            const isLocked =
                activeSellerId &&
                group.dataset.sellerId !== activeSellerId;

            group.classList.toggle('is-locked', isLocked);

            group.querySelectorAll('input, button').forEach(el => {

                if (!el.classList.contains('item-selector')) {
                    el.disabled = isLocked;
                }
            });

            const checkbox = group.querySelector('.item-selector');

            if (checkbox && checkbox.checked) {

                subtotal += parseFloat(
                    checkbox.dataset.price || 0
                );
            }
        });

        // 運費
        const shipping = subtotal > 0 ? 60 : 0;

        const total = subtotal + shipping;

        // 更新畫面
        const subtotalEl =
            document.getElementById('display_subtotal');

        const totalEl =
            document.getElementById('display_total');

        if (subtotalEl) {
            subtotalEl.textContent =
                subtotal.toLocaleString();
        }

        if (totalEl) {
            totalEl.textContent =
                total.toLocaleString();
        }
    }

    // checkbox 勾選事件
    document.addEventListener('change', function(e) {

        if (e.target.classList.contains('item-selector')) {
            updateLockState();
        }
    });

    // 2. 移除商品與數量調整邏輯
    const modalEl = document.getElementById('removeItemConfirmModal');
    const removeModal = (modalEl && typeof bootstrap !== 'undefined') ? new bootstrap.Modal(modalEl) : null;
    const removeItemName = document.getElementById('removeItemName');
    const confirmBtn = document.getElementById('confirmRemoveItemBtn');
    let pendingForm = null;

    // 處理減號按鈕 (若數量減至 0 觸發 Modal)
    document.querySelectorAll('.decrease-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (parseInt(this.dataset.nextQuantity, 10) === 0) {
                e.preventDefault();
                pendingForm = this.closest('form');
                if(removeItemName) removeItemName.textContent = this.dataset.itemName || '此商品';
                if(removeModal) removeModal.show();
            }
        });
    });

    // 處理數字框調整
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.dataset.previousValue = input.value;
        input.addEventListener('change', function() {
            const quantity = parseInt(this.value, 10) || 0;
            const form = this.closest('form');
            if (quantity === 0) {
                this.value = this.dataset.previousValue;
                let hiddenInput = form.querySelector('.remove-quantity-input');
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'quantity';
                    hiddenInput.value = 0;
                    hiddenInput.classList.add('remove-quantity-input');
                    form.appendChild(hiddenInput);
                }
                pendingForm = form;
                if(removeItemName) removeItemName.textContent = this.dataset.itemName || '此商品';
                if(removeModal) removeModal.show();
            } else {
                this.dataset.previousValue = this.value;
                form.submit();
            }
        });
    });

    // 確認刪除按鈕
    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => { if (pendingForm) pendingForm.submit(); });
    }

    // 頁面載入時執行一次狀態檢查
    updateLockState();
    
});

function prepareCheckout() {

    const checkedBoxes = document.querySelectorAll('.item-selector:checked');

    if (checkedBoxes.length === 0) {
        alert('請先勾選要結帳的商品');
        return;
    }
    const sellerId =
    checkedBoxes[0].dataset.sellerId.replace('seller_', '');

    document.getElementById('selected_seller_id').value =
        sellerId;

    let listHtml = '';
    let subtotal = 0;

    let followIds = [];
    let requestIds = [];

    checkedBoxes.forEach(cb => {

        const title = cb.dataset.title || '商品';

        const price = parseFloat(cb.dataset.price || 0);

        subtotal += price;

        listHtml += `
            <div class="d-flex justify-content-between mb-2">
                <small>${title}</small>
                <small>NT$ ${price.toLocaleString()}</small>
            </div>
        `;

        if (cb.dataset.type === 'follow') {
            followIds.push(cb.value);
        }

        if (cb.dataset.type === 'request') {
            requestIds.push(cb.value);
        }
    });

    document.getElementById('checkout_items_list').innerHTML = listHtml;

    document.getElementById('modal_total_amount').textContent =
        (subtotal + 60).toLocaleString();

    document.getElementById('selected_follow_orders').value =
        followIds.join(',');

    document.getElementById('selected_request_lists').value =
        requestIds.join(',');

    fetch(`/logistics/by-seller/${sellerId}`)
    .then(res => res.json())
    .then(data => {

        const select =
            document.querySelector('select[name="logistics_id"]');

        select.innerHTML = '';

        // 沒有物流
        if (data.length === 0) {

            select.innerHTML = `
                <option value="">
                    該代購人尚未設定物流
                </option>
            `;

        } else {

            data.forEach(item => {

                select.innerHTML += `
                    <option value="${item.id}">
                        ${item.name} (${item.temp_layer})
                    </option>
                `;
            });
        }

        new bootstrap.Modal(
            document.getElementById('checkoutModal')
        ).show();
    });

}
</script>


@endsection