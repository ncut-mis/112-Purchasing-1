{{-- resources/views/shop/store.blade.php --}}
@extends('layouts.furni')

@section('hero')
<div class="hero">
    <div class="container">
        <div class="row justify-content-between">
            <div class="col-lg-5">
                <div class="intro-excerpt">
                    <h1>找代購</h1>
                    @if(request('search'))
                        <p class="lead">搜尋「{{ request('search') }}」找到 {{ $posts->total() }} 筆</p>
                    @else
                        <p class="lead">所有代購貼文 {{ $posts->total() }} 筆</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="untree_co-section product-section before-footer-section">
    <div class="container">
        <!-- 原本的搜尋表單 + 發布按鈕 -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <form action="{{ route('store') }}" method="GET" class="d-flex gap-3">
                        <input type="text" name="search" class="form-control shadow-sm" 
                               placeholder="輸入商品關鍵字" value="{{ request('search') }}" 
                               style="min-width: 250px;">
                        <button type="submit" class="btn btn-primary-custom btn-lg shadow-sm">
                        <i class="bi bi-search me-2"></i>
                        </button>
                    </form> 
                    
                    @auth
                        <a href="{{ route('posts.create') }}" class="btn btn-white bg-white text-dark btn-lg shadow-sm rounded-pill border">
                            <i class="bi bi-pencil-square me-2"></i>發布許願
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-white bg-white text-dark btn-lg shadow-sm rounded-pill border">
                            <i class="bi bi-pencil-square me-2"></i>發布許願
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- 原本的卡片列表 -->
        <div class="row">
            @forelse($posts as $post)
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $post->title }}</h5>
                            <p class="card-text">{{ Str::limit($post->description, 100) }}</p>
                            <p class="text-muted small mb-1">
                                🏠 {{ $post->region }} | 
                                📦 {{ $post->max_quantity }} 件 | 
                                ⏰ {{ $post->deadline }}
                            </p>
                            <span class="badge bg-success">{{ $post->status }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="alert alert-info">
                            沒有找到符合條件的代購貼文
                        </div>
                        <a href="{{ route('store') }}" class="btn btn-primary-custom">看所有貼文</a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- 分頁 -->
        {{ $posts->appends(request()->query())->links() }}
    </div>
</div>
@endsection
