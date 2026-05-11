{{-- resources/views/shoppingcart.blade.php --}}
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
                    <span class="display-3 thankyou-icon text-primary mb-5">
                        <svg width="3rem" height="3rem" viewBox="0 0 16 16" class="bi bi-cart-check" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.354 5.646a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L8 8.293l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                            <path fill-rule="evenodd" d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm7 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                        </svg>
                    </span>
                    <h2 class="display-3 text-black">感謝訂購！</h2>
                    <p class="lead mb-5">{{ Session::get('success') }}</p>
                    <p><a href="{{ route('shop') }}" class="btn btn-sm btn-outline-black">返回商店</a></p>
                </div>
            </div>
        @else
            <div class="row justify-content-center">
                <div class="col-md-10">
                    {{-- 合併處理 Session 購物車與資料庫購物車 --}}
                    @php
                        $sessionCart = session('cart', []);
                        $totalCount = $cartItems->count() + count($sessionCart);
                        $subtotal = 0;
                    @endphp

                    @if($totalCount == 0)
                        <div class="row text-center py-5">
                            <div class="col-md-12">
                                <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                                <h3>您的結帳區是空的</h3>
                                <p class="text-muted">快去挑選喜歡的商品或查看報價吧！</p>
                                <a href="/dashboard" class="btn btn-primary btn-lg">回我的儀表板</a>
                            </div>
                        </div>
                    @else
                        <div class="row mb-5">
                            <div class="col-md-12 border-bottom pb-3">
                                <h2 class="h3">待結帳項目 <span class="text-primary">({{ $totalCount }})</span></h2>
                            </div>
                        </div>

                        {{-- 1. 顯示來自報價單的項目 (Session) --}}
                        @foreach($sessionCart as $id => $details)
                            @php $subtotal += $details['price'] * $details['quantity']; @endphp
                            <div class="row mb-4 align-items-center bg-light p-3 rounded-3 shadow-sm border-start border-4 border-amber-500">
                                <div class="col-md-2">
                                    <div class="bg-amber-100 text-amber-600 rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                                        <i class="fas fa-file-invoice-dollar fa-3x"></i>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <span class="badge bg-amber-500 mb-2">代購委託</span>
                                    <h5 class="text-dark">{{ $details['name'] }}</h5>
                                    <p class="text-muted small mb-0">代購人：{{ $details['agent_name'] }}</p>
                                    <div class="mt-2">
                                        @foreach($details['items'] as $innerItem)
                                            <span class="badge bg-white text-slate-600 border mr-1">{{ $innerItem }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-2 text-center">
                                    <h5 class="text-primary font-weight-bold">${{ number_format($details['price'], 0) }}</h5>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span>數量: {{ $details['quantity'] }}</span>
                                </div>
                                <div class="col-md-2 text-end">
                                    <h5 class="text-success">${{ number_format($details['price'] * $details['quantity'], 0) }}</h5>
                                    {{-- 這裡可以補一個從 session 移除的 route --}}
                                </div>
                            </div>
                        @endforeach

                        {{-- 2. 顯示一般商品項目 (Database) --}}
                        @foreach($cartItems as $item)
                            @php $subtotal += $item->quantity * $item->product->price; @endphp
                            <div class="row mb-4 align-items-center border-bottom pb-4">
                                <div class="col-md-2">
                                    <img src="{{ asset('storage/' . $item->product->image) }}" class="img-fluid rounded shadow-sm">
                                </div>
                                <div class="col-md-4">
                                    <h5 class="text-dark">{{ $item->product->name }}</h5>
                                    <p class="text-muted small">{{ Str::limit($item->product->description, 50) }}</p>
                                </div>
                                <div class="col-md-2 text-center">
                                    <h5 class="text-primary">${{ number_format($item->product->price, 0) }}</h5>
                                </div>
                                <div class="col-md-2">
                                    <form action="{{ route('cart.update', $item->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" class="form-control form-control-sm text-center">
                                    </form>
                                </div>
                                <div class="col-md-2 text-end">
                                    <h5 class="text-success">${{ number_format($item->quantity * $item->product->price, 0) }}</h5>
                                </div>
                            </div>
                        @endforeach

                        
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection