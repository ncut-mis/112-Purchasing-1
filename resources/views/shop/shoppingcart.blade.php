{{-- resources/views/shoppingcart.blade.php --}}
@extends('layouts.furni')

@section('content')
<!-- Start Hero Section -->
<div class="hero">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-lg-5">
                <div class="intro-excerpt">
                    <h1>購物車</h1>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="untree_co-section">
    <div class="container">
        @if(Session::has('success'))
            <!-- 訂單成功頁面 -->
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
                    <p><a href="{{ route('shop.index') }}" class="btn btn-sm btn-outline-black">返回商店</a></p>
                </div>
            </div>
        @else
            <!-- 購物車主內容 -->
            <div class="row justify-content-center">
                <div class="col-md-10">
                    @if($cartItems->isEmpty())
                        <!-- 空的購物車 -->
                        <div class="row text-center py-5">
                            <div class="col-md-12">
                                <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                                <h3>您的購物車是空的</h3>
                                <p class="text-muted">快去挑選喜歡的商品吧！</p>
                                <a href="/store" class="btn btn-primary btn-lg">前往貼文</a>
                            </div>
                        </div>
                    @else
                        <!-- 商品列表 -->
                        <div class="row mb-5">
                            <div class="col-md-12">
                                <h2 class="mb-4">購物車 <span class="text-primary">({{ $cartItems->count() }} 項)</span></h2>
                            </div>
                        </div>

                        @foreach($cartItems as $item)
                        <div class="row mb-4 align-items-center">
                            <div class="col-md-2">
                                <img src="{{ asset('storage/' . $item->product->image) }}" 
                                     alt="{{ $item->product->name }}" 
                                     class="img-fluid rounded">
                            </div>
                            <div class="col-md-4">
                                <h5>{{ $item->product->name }}</h5>
                                <p class="text-muted small">{{ Str::limit($item->product->description, 100) }}</p>
                            </div>
                            <div class="col-md-2 text-center">
                                <h5 class="text-primary">${{ number_format($item->product->price, 0) }}</h5>
                            </div>
                            <div class="col-md-2">
                                <form action="{{ route('cart.update', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" 
                                               min="1" max="{{ $item->product->stock }}" class="form-control">
                                        <button type="submit" class="btn btn-outline-primary btn-sm">更新</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-2 text-end">
                                <h5 class="text-success">
                                    ${{ number_format($item->quantity * $item->product->price, 0) }}
                                </h5>
                                <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="d-inline mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('確定要刪除嗎？')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach

                        <!-- 結帳總計 -->
                        <div class="row justify-content-end mt-5">
                            <div class="col-md-4">
                                <div class="card shadow-sm">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">訂單總計</h4>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span>商品總計:</span>
                                            <span class="h5">${{ number_format($subtotal, 0) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span>運費:</span>
                                            <span class="h6">${{ number_format($shipping, 0) }}</span>
                                        </div>
                                        <hr class="my-3">
                                        <div class="d-flex justify-content-between mb-4">
                                            <span class="h4">總計:</span>
                                            <span class="h3 text-primary">${{ number_format($total, 0) }}</span>
                                        </div>
                                        <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg w-100">
                                            前往結帳 <i class="fas fa-arrow-right ms-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
