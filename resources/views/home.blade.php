@extends('layouts.front')

@section('content')

<!-- Hero Header (參考 Tiya 的 Hero Section) -->
<section class="hero-section position-relative d-flex align-items-center" style="min-height: 500px; background: linear-gradient(135deg, #e0f7fa 0%, #f3f7f5 100%);">
    <div class="container">
        <div class="row align-items-center">
           <div class="col-lg-6">
                <span class="badge bg-white text-success shadow-sm px-3 py-2 rounded-pill mb-3">
                    <i class="bi bi-star-fill me-1"></i> 全球連線中
                </span>
                <h1 class="display-4 fw-bold mb-3" style="color: #2c3e50;">
                    發現世界各地<br>
                    <span style="color: var(--primary-color);">獨一無二</span> 的好物
                </h1>
                <p class="lead text-muted mb-4">
                    連結數千位優質代購人，無論是日本藥妝、歐洲精品還是韓國服飾，我們都能幫您帶回家。
                </p>
                <div class="flex gap-3">
                    <form action="{{ route('agent.posts.search') }}" method="GET" class="d-flex gap-3 align-items-end">
                        <input type="text" 
                            name="search" 
                            class="form-control shadow-sm" 
                            placeholder="輸入代購關鍵字（如 iPhone、日本）" 
                            value="{{ request('q') }}" 
                            style="width: 300px;">
                        <button type="submit" class="btn btn-primary-custom btn-lg shadow-sm h-100">
                            <i class="bi bi-search me-2"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-lg-6 text-center d-none d-lg-block">
                <!-- 這裡使用假圖，實際上可以放一張漂亮的插畫 -->
                <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Shopping" class="img-fluid rounded-4 shadow-lg" style="transform: rotate(-3deg);">
            </div>
        </div>
    </div>
</section>

<!-- Agent Posts Section (最新代購連線) -->
<section class="py-5">
    <div class="container">
        @if(request()->has('search'))
            {{-- 搜尋結果標題 --}}
            <div class="alert alert-info rounded-4 mb-4 border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="bi bi-search fs-3 me-3 text-primary"></i>
                    <div>
                        <h4 class="mb-1 fw-bold">搜尋「{{ request('search') }}」</h4>
                        <p class="mb-0">找到 <strong>{{ $agentPosts->total() }}</strong> 筆結果</p>
                    </div>
                    <a href="{{ route('agent.posts.search') }}" class="ms-auto btn btn-sm btn-outline-primary rounded-pill px-3">
                        清除搜尋
                    </a>
                </div>
            </div>
        @else
            {{-- 原本的最新代購標題 --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="text-success fw-bold text-uppercase mb-1">Agent Posts</h6>
                    <h2 class="fw-bold">最新代購連線</h2>
                </div>
                <a href="{{ route('store') }}" class="text-decoration-none text-muted">查看全部 <i class="bi bi-arrow-right"></i></a>
            </div>
        @endif


        <div class="row g-4">
             @forelse($agentPosts as $agentPost)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="position-relative" style="height: 210px;">
                            @php
                                 $firstProduct = $agentPost->products->first();
                            @endphp
                             @if(optional($firstProduct)->image_path)
                                <img src="{{ route('post-product.image', $firstProduct) }}" alt="{{ $agentPost->title }} 商品圖片" class="w-100 h-100 object-fit-cover">
                            @elseif($agentPost->cover_image)
                                <img src="{{ asset('storage/' . $agentPost->cover_image) }}" alt="{{ $agentPost->title }}" class="w-100 h-100 object-fit-cover">
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-secondary">
                                    <i class="bi bi-image fs-1"></i>
                                </div>
                            @endif
                            <span class="position-absolute top-0 start-0 m-3 badge rounded-pill bg-dark-subtle text-dark">
                                {{ $agentPost->country }}{{ $agentPost->city ? '・' . $agentPost->city : '' }}
                            </span>
                            <span class="position-absolute top-0 end-0 m-3 badge rounded-pill bg-success">
                                {{ $agentPost->status === 'open' ? '接單中' : $agentPost->status }}
                            </span>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="card-title fw-bold mb-2">{{ $agentPost->title }}</h5>
                            <p class="text-muted small mb-3" style="min-height: 48px;">
                                {{ \Illuminate\Support\Str::limit($agentPost->description ?: '代購人尚未填寫詳細說明。', 80) }}
                            </p>

                            <div class="small text-muted bg-light rounded-3 p-3 mb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-calendar-event me-2"></i>
                                    <span>代購期間：{{ optional($agentPost->start_date)->format('Y/m/d') }} - {{ optional($agentPost->end_date)->format('Y/m/d') }}</span>
                                </div>
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-truck me-2"></i>
                                    <span>預計出貨：{{ optional($agentPost->estimated_shipping_date)->format('Y/m/d') ?? '未提供' }}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-box-seam me-2"></i>
                                    <span>可代購商品：{{ $agentPost->products->count() }} 項</span>
                                </div>
                            </div>

                           <div class="d-flex flex-wrap gap-2 mb-3">
                                @forelse($agentPost->products->take(3) as $product)
                                    <span class="badge rounded-pill text-bg-light border">{{ $product->name }}</span>
                                @empty
                                    <span class="badge rounded-pill text-bg-light border">尚未建立商品明細</span>
                                @endforelse
                            </div>

                            <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top">
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(optional($agentPost->user)->name ?? 'Agent') }}&background=random" class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                    <div>
                                        <div class="small fw-semibold text-dark">{{ optional($agentPost->user)->name ?? '匿名代購人' }}</div>
                                        <div class="small text-muted">已建立於 {{ optional($agentPost->created_at)->format('Y/m/d') }}</div>
                                    </div>
                                </div>
                              @auth
                                       @if((int) auth()->id() === (int) $agentPost->user_id)
                                           <span class="small text-danger fw-semibold text-end ms-3">你無法對自己所發布的代購貼文進行跟單😅</span>
                                       @else
                                           <a href="#" class="btn btn-sm btn-primary-custom rounded-pill px-3">我要跟單</a>
                                       @endif
                                   @else
                                       <a href="#" class="btn btn-sm btn-primary-custom rounded-pill px-3">我要跟單</a>
                              @endauth
                            </div>
                        </div>
                    </div>
                </div>
           @empty
                @if(request()->has('search'))
                    <div class="col-12">
                        <div class="text-center py-12 bg-light rounded-4 border-dashed border-2 border-warning">
                            <i class="bi bi-search display-1 text-muted mb-4 opacity-50"></i>
                            <h3 class="text-muted mb-3">沒有找到符合條件的代購貼文</h3>
                            <p class="text-muted mb-4">請嘗試：</p>
                            <ul class="text-start text-muted mb-0">
                                <li>使用其他關鍵字（如商品名稱、國家）</li>
                                <li>檢查拼字是否正確</li>
                                <li><a href="{{ route('agent.posts.search') }}" class="text-primary fw-bold">清除搜尋條件</a></li>
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="col-12">
                        <div class="rounded-4 border border-dashed p-5 text-center text-muted bg-light">
                            目前尚無最新代購連線，歡迎代購人前往會員專區建立貼文。
                        </div>
                    </div>
                @endif
            @endforelse
        </div>
    </div>
</section>

<!-- Section 2: 許願清單 (List/Grid Style) -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h6 class="text-success fw-bold text-uppercase ls-1">Requests</h6>
            <h2 class="fw-bold">大家都在找什麼？</h2>
            <p class="text-muted">您可以接單這些請求，賺取代購費</p>
        </div>

        
        
        <div class="text-center mt-5">
            <a href="{{ route('store') }}" class="btn btn-outline-dark rounded-pill px-5 py-2">瀏覽所有貼文</a>
        </div>
    </div>
</section>

<!-- Newsletter / CTA Section -->
<section class="py-5">
    <div class="container">
        <div class="bg-primary p-5 rounded-4 text-white text-center" style="background: linear-gradient(45deg, #5A9E8E, #3b7d6e);">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="fw-bold mb-3">準備好開始代購了嗎？</h2>
                    <p class="lead mb-4 opacity-75">無論您是想買東西，還是即將出國想順便賺旅費，這裡都是您的最佳選擇。</p>
                    <a href="{{ route('register') }}" class="btn btn-light text-success btn-lg rounded-pill px-5 fw-bold shadow">
                        免費註冊會員
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection