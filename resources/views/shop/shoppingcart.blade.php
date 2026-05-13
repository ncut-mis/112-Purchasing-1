{{-- resources/views/shop/shoppingcart.blade.php --}}
@extends('layouts.furni')

@section('content')
<div class="hero">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-lg-5">
                <div class="intro-excerpt">
                    <h1>結帳區</h1>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="untree_co-section">
    <div class="container">
        @if(Session::has('success'))
            <div class="row">
                <div class="col-md-12 text-center pt-5">
                    <h2 class="display-3 text-black">感謝訂購！</h2>
                    <p class="lead mb-5">{{ Session::get('success') }}</p>
                      <p><a href="{{ route('store.index') }}" class="btn btn-sm btn-outline-black">返回商店</a></p>
                    <p><a href="/request-lists" class="btn btn-sm btn-outline-black">返回商店</a></p>
                </div>
            </div>
        @else
            <div class="row justify-content-center">
                <div class="col-md-10">
                    @php
                        /* 關鍵修正：確保變數存在，若不存在則給予空集合避免報錯 */
                        $items = $cartItems ?? collect();
                        $requests = $requestLists ?? collect(); 
                        $sessionCartItems = count(session('cart', []));
                        $matchedRequestsCount = ($requestLists ?? collect())->where('status', 'matched')->count();
                        $totalCount = $items->count() + $matchedRequestsCount + $sessionCartItems;
                    @endphp

                    @if($totalCount == 0)
                        <div class="row text-center py-5">
                            <div class="col-md-12">
                                <h3>您的結帳區是空的</h3>
                                <a href="/dashboard" class="btn btn-primary btn-lg">回我的會員專區</a>
                            </div>
                        </div>
                    @else
                        <div class="row mb-5">
                            <div class="col-md-12 border-bottom pb-3">
                                <h2 class="h3">待結帳項目 <span class="text-primary">({{ $totalCount }})</span></h2>
                            </div>
                        </div>

                        
                      

                        {{-- 2. 顯示一般商品項目 (CartItems) --}}
                       {{-- 尋找這段 foreach 並修改內容 --}}
@foreach($requests as $list)
    {{-- 關鍵判斷：只有已媒合(matched)的單子才出現在結帳區 --}}
    @if($list->status === 'matched')
        <div class="row mb-4 align-items-center bg-light p-3 rounded-3 shadow-sm border-start border-4 border-info">
            <div class="col-md-2 text-center">
                <div class="bg-info text-white rounded p-3">
                    <i class="fas fa-clipboard-list fa-2x"></i>
                </div>
            </div>
            <div class="col-md-4">
                <span class="badge bg-info mb-2">代購需求項目</span>
                <h5 class="text-dark">{{ $list->title }}</h5>
                
                {{-- 顯示代購人名字 --}}
                <p class="mb-0 small text-muted">
                    代購人：{{ $list->agent->name ?? '代購編號 #' . $list->people }}
                </p>
            </div>
            <div class="col-md-2 text-center">
                {{-- 顯示代購報價總額 --}}
                <h5 class="text-primary font-weight-bold">
                    ${{ number_format($list->agent_quote_total, 0) }}
                </h5>
            </div>
            <div class="col-md-2 text-center">數量: 1</div>
            <div class="col-md-2 text-end">
                <h5 class="text-success">
                    ${{ number_format($list->agent_quote_total, 0) }}
                </h5>
            </div>
        </div>
    @endif
@endforeach

                        {{-- 總計與結帳按鈕 --}}
                        <div class="row mt-5">
                            <div class="col-md-12 text-end">
                                <h4 class="text-black">總計金額：<strong>${{ number_format($total ?? 0, 0) }}</strong></h4>
                                <a href="/checkout" class="btn btn-primary btn-lg mt-3">前往結帳</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection