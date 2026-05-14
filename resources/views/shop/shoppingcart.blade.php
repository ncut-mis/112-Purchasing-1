{{-- resources/views/shop/shoppingcart.blade.php --}}
@extends('layouts.furni')

@section('content')
<div class="untree_co-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                
                {{-- A 區塊：跟單商品 (Primary 藍色系) --}}

                @if($followOrders->isNotEmpty())
                    <div class="mb-5">
                        <h4 class="text-info fw-bold mb-3"><i class="bi bi-file-earmark-text-fill"></i> 專屬團代購團</h4>
                        @foreach($followOrders as $list) {{-- 這裡你定義了變數叫 $list --}}
                            <div class="row mb-4 align-items-center bg-white p-3 rounded-3 shadow-sm border-start border-5 border-info">
                                <div class="col-md-7">
                                    <span class="badge bg-info mb-3 text-white">需求單</span>
                                    <h5 class="text-dark">{{ $list->title }}</h5>
                                    <p class="mb-2 small text-muted">
                                        {{-- 關鍵修正：將 $order 改成 $list --}}
                                        代購人：{{ $list->seller->name ?? '系統匹配' }}
                                    </p>
                                </div>
                                <div class="col-md-5 text-end">
                                    <h5 class="text-success fw-bold">NT$ {{ number_format($list->total_amount, 0) }}</h5>
                                </div>
                                <div class="col-md-3 text-end">
                                    {{-- 跟單退回按鈕 --}}
                                    <form action="{{ route('order.cancel', $list->id) }}" method="POST" onsubmit="return confirm('確定要移除這項跟單商品嗎？')">
                                        @csrf
                                        @method('DELETE') {{-- 注意這裡要用 DELETE 方法 --}}
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-trash me-1"></i>移除項目
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- B 區塊：代購報價單 (Info 青色系) --}}
               
                @if($requestLists->isNotEmpty())
    <div class="mb-5">
        <h4 class="text-info fw-bold mb-3">
            <i class="bi bi-file-earmark-text-fill me-2"></i>專屬代購報價單
        </h4>
        @foreach($requestLists as $list)
            <div class="row mb-3 p-3 bg-white shadow-sm border-start border-5 border-info rounded align-items-center">
                <div class="col-md-7">
                    <span class="badge bg-info mb-2 text-white">需求單</span>
                    <h5 class="mb-1 text-dark">{{ $list->title }}</h5>
                    
                    {{-- 顯示 People 模型裡的名字 --}}
                    <p class="mb-0 small text-muted">
                        代購人：{{ $list->agent->name ?? '系統匹配' }}
                    </p>
                </div>
                <div class="col-md-5 text-end">
                    <h5 class="text-success fw-bold">NT$ {{ number_format($list->agent_quote_total, 0) }}</h5>
                </div>
            </div>
        @endforeach
    </div>
@endif

                {{-- 結帳按鈕與金額 --}}
                @if($followOrders->isEmpty() && $requestLists->isEmpty())
                    <div class="text-center py-5">
                        <h3>您的結帳區是空的</h3>
                        <a href="/dashboard" class="btn btn-primary mt-3">回個人專區</a>
                    </div>
                @else
                    <div class="row mt-5 border-top pt-4">
                        <div class="col-md-12 text-end">
                            <p class="mb-1">商品小計：NT$ {{ number_format($subtotal, 0) }}</p>
                            <p class="mb-2">預計運費：NT$ 60</p>
                            <h3 class="text-black">結帳總計：<strong class="text-success h2">NT$ {{ number_format($total, 0) }}</strong></h3>
                            <button type="button" class="btn btn-dark w-100" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                                立即前往支付
                            </button>
                        </div>
                    </div>
                @endif

            </div>
            <div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title w-100 text-center">結帳</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="checkoutForm" action="{{ route('checkout.process') }}" method="POST">
                    @csrf
                    
                    <h6>付款詳情</h6>
                    <div class="border p-3 mb-3">
                        @foreach($followOrders as $order)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-light text-dark border p-2">{{ $order->seller->name ?? '代購商品' }}</span>
                            <small>單價:{{ number_format($order->total_amount / ($order->quantity ?: 1)) }} 數量:{{ $order->quantity ?: 1 }} 總計:{{ number_format($order->total_amount) }}</small>
                        </div>
                        @endforeach
                        
                        {{-- 如果有報價單也一併顯示 --}}
                        @foreach($requestLists as $list)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-light text-dark border p-2">{{ $list->title }}</span>
                            <small>總計:{{ number_format($list->agent_quote_total) }}</small>
                        </div>
                        @endforeach
                        
                        <div class="text-end fw-bold mt-2 border-top pt-2">
                            總計 : {{ number_format($subtotal) }}
                        </div>
                    </div>

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

                    <div class="text-end">
                        <p class="mb-0">運費 + 60</p>
                        <h5 class="text-danger">總計 : {{ number_format($total) }}</h5>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-secondary px-5">確定</button>
                        <button type="button" class="btn btn-light border px-5" data-bs-dismiss="modal">取消</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>
@endsection