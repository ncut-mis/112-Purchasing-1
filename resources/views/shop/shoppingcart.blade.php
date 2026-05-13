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
                            <p class="mb-2">運費：NT$ 100</p>
                            <h3 class="text-black">結帳總計：<strong class="text-success h2">NT$ {{ number_format($total, 0) }}</strong></h3>
                            <button class="btn btn-primary btn-lg mt-3 px-5 shadow">立即前往支付</button>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection