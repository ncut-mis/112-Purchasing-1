{{-- resources/views/shop/shoppingcart.blade.php --}}
@extends('layouts.furni')

@section('content')

<div class="untree_co-section">
    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-10">

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

                            <div class="card mb-4 bg-white shadow-sm border-start border-5 border-info rounded-3">

                                {{-- Header --}}
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">

                                    <div>
                                        <span class="small text-muted">
                                            代購人：{{ $order->seller->name ?? '系統匹配' }}
                                        </span>
                                    </div>

                                    {{-- 移除整筆訂單 --}}
                                    <form action="{{ route('order.cancel', $order->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('確定要移除這筆代購訂單與裡面的所有商品嗎？')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-link text-danger btn-sm p-0 text-decoration-none">

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
                                                        <td class="ps-3">

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
                                                        <td class="text-end pe-3 fw-bold text-success">

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

                            <div class="row mb-3 p-3 bg-white shadow-sm border-start border-5 border-info rounded align-items-center">

                                <div class="col-md-7">

                                    <span class="badge bg-info mb-2 text-white">
                                        需求單
                                    </span>

                                    <h5 class="mb-1 text-dark">
                                        {{ $list->title }}
                                    </h5>

                                    <p class="mb-0 small text-muted">

                                        代購人：
                                        {{ $list->agent->name ?? '系統匹配' }}

                                    </p>

                                </div>

                                <div class="col-md-5 text-end">

                                    <h5 class="text-success fw-bold">

                                        NT$
                                        {{ number_format($list->agent_quote_total, 0) }}

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

                        <div class="col-md-12 text-end">

                            <p class="mb-1">
                                商品小計：
                                NT$
                                {{ number_format($subtotal, 0) }}
                            </p>

                            <p class="mb-2">
                                預計運費：NT$ 60
                            </p>

                            <h3 class="text-black">

                                結帳總計：

                                <strong class="text-success h2">

                                    NT$
                                    {{ number_format($total, 0) }}

                                </strong>

                            </h3>

                            <button type="button"
                                    class="btn btn-dark w-100"
                                    data-bs-toggle="modal"
                                    data-bs-target="#checkoutModal">

                                立即前往支付

                            </button>

                        </div>

                    </div>

                @endif

            </div>

        </div>

    </div>
</div>

{{-- ========================= --}}
{{-- 結帳 Modal --}}
{{-- ========================= --}}
<div class="modal fade"
     id="checkoutModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-md">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title w-100 text-center">
                    結帳
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>

            <div class="modal-body">

                <form id="checkoutForm"
                      action="{{ route('checkout.process') }}"
                      method="POST">

                    @csrf

                    {{-- 付款詳情 --}}
                    <h6>付款詳情</h6>

                    <div class="border p-3 mb-3">

                        @foreach($followOrders as $order)

                            <div class="d-flex justify-content-between align-items-center mb-2">

                                <span class="badge bg-light text-dark border p-2">

                                    {{ $order->seller->name ?? '代購商品' }}

                                </span>

                                <small>

                                    總計:
                                    {{ number_format($order->total_amount) }}

                                </small>

                            </div>

                        @endforeach

                        @foreach($requestLists as $list)

                            <div class="d-flex justify-content-between align-items-center mb-2">

                                <span class="badge bg-light text-dark border p-2">

                                    {{ $list->title }}

                                </span>

                                <small>

                                    總計:
                                    {{ number_format($list->agent_quote_total) }}

                                </small>

                            </div>

                        @endforeach

                        <div class="text-end fw-bold mt-2 border-top pt-2">

                            總計 :
                            {{ number_format($subtotal) }}

                        </div>

                    </div>

                    {{-- 付款方式 --}}
                    <h6>付款方式</h6>

                    <div class="d-flex justify-content-between mb-3">

                        <input type="radio"
                               class="btn-check"
                               name="payment_method"
                               id="pay1"
                               value="linepay"
                               checked>

                        <label class="btn btn-outline-secondary flex-grow-1 mx-1"
                               for="pay1">

                            LINE Pay

                        </label>

                        <input type="radio"
                               class="btn-check"
                               name="payment_method"
                               id="pay2"
                               value="bank">

                        <label class="btn btn-outline-secondary flex-grow-1 mx-1"
                               for="pay2">

                            超商付款

                        </label>

                        <input type="radio"
                               class="btn-check"
                               name="payment_method"
                               id="pay3"
                               value="jkopay">

                        <label class="btn btn-outline-secondary flex-grow-1 mx-1"
                               for="pay3">

                            街口支付

                        </label>

                    </div>

                    {{-- 地址 --}}
                    <div class="mb-3">

                        <label class="form-label">
                            送貨地址：
                        </label>

                        <input type="text"
                               name="address"
                               class="form-control"
                               required
                               placeholder="請輸入收件地址">

                    </div>

                    {{-- 物流 --}}
                    <div class="mb-3">

                        <label class="form-label">
                            物流：
                        </label>

                        <select name="logistics_id"
                                class="form-select">

                            @foreach($logistics as $item)

                                <option value="{{ $item->id }}">

                                    {{ $item->name }}
                                    ({{ $item->temp_layer }})

                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- 總計 --}}
                    <div class="text-end">

                        <p class="mb-0">
                            運費 + 60
                        </p>

                        <h5 class="text-danger">

                            總計 :
                            {{ number_format($total) }}

                        </h5>

                    </div>

                    {{-- 按鈕 --}}
                    <div class="d-flex justify-content-between mt-4">

                        <button type="submit"
                                class="btn btn-secondary px-5">

                            確定

                        </button>

                        <button type="button"
                                class="btn btn-light border px-5"
                                data-bs-dismiss="modal">

                            取消

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

{{-- ========================= --}}
{{-- JS --}}
{{-- ========================= --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const modalEl =
        document.getElementById('removeItemConfirmModal');

    if (!modalEl || typeof bootstrap === 'undefined') return;

    const removeModal =
        new bootstrap.Modal(modalEl);

    const removeItemName =
        document.getElementById('removeItemName');

    const confirmBtn =
        document.getElementById('confirmRemoveItemBtn');

    let pendingForm = null;

    // =========================
    // 減號按鈕
    // =========================
    document.querySelectorAll('.decrease-btn').forEach((btn) => {

        btn.addEventListener('click', function (e) {

            const nextQuantity =
                parseInt(this.dataset.nextQuantity, 10);

            if (nextQuantity === 0) {

                e.preventDefault();

                pendingForm =
                    this.closest('form');

                removeItemName.textContent =
                    this.dataset.itemName || '此商品';

                removeModal.show();
            }
        });
    });

    // =========================
    // 數字框調整
    // =========================
    document.querySelectorAll('.quantity-input').forEach((input) => {

        input.dataset.previousValue = input.value;

        input.addEventListener('focus', function () {

            this.dataset.previousValue =
                this.value;
        });

        input.addEventListener('change', function () {

            const quantity =
                parseInt(this.value, 10) || 0;

            const form =
                this.closest('form');

            const itemName =
                this.dataset.itemName || '此商品';

            // 調整成 0
            if (quantity === 0) {

                // 還原原本數量
                this.value =
                    this.dataset.previousValue || 1;

                // 建立隱藏 input 送出 0
                let hiddenInput =
                    form.querySelector('.remove-quantity-input');

                if (!hiddenInput) {

                    hiddenInput =
                        document.createElement('input');

                    hiddenInput.type = 'hidden';

                    hiddenInput.name = 'quantity';

                    hiddenInput.value = 0;

                    hiddenInput.classList.add('remove-quantity-input');

                    form.appendChild(hiddenInput);
                }

                pendingForm = form;

                removeItemName.textContent = itemName;

                removeModal.show();

            } else {

                this.dataset.previousValue =
                    this.value;

                form.submit();
            }
        });
    });

    // =========================
    // 確認移除
    // =========================
    if (confirmBtn) {

        confirmBtn.addEventListener('click', function () {

            if (pendingForm) {

                pendingForm.submit();
            }
        });
    }

});

</script>

@endsection