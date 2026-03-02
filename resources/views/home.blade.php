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
                <div class="d-flex gap-3">
                    <a href="#" class="btn btn-primary-custom btn-lg shadow-sm">
                        <i class="bi bi-search me-2"></i>搜尋代購
                    </a>
                    <a href="#" class="btn btn-white bg-white text-dark btn-lg shadow-sm rounded-pill border">
                        <i class="bi bi-pencil-square me-2"></i>發布許願
                    </a>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h6 class="text-success fw-bold text-uppercase mb-1">Agent Posts</h6>
                <h2 class="fw-bold">最新代購連線</h2>
            </div>
            <a href="#" class="text-decoration-none text-muted">查看全部 <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-4">
            @foreach($posts as $post)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 position-relative hover-shadow transition">
                    <!-- 地區標籤 -->
                    <span class="position-absolute top-0 end-0 m-3 badge rounded-pill bg-warning text-dark">
                        {{ $post->region }}
                    </span>
                    
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <!-- 預設圖片或是佔位圖 -->
                            <div class="rounded-4 bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-box-seam text-secondary display-4"></i>
                            </div>
                        </div>

                        <!-- 標題 -->
                        <h5 class="card-title fw-bold mb-2">{{ $post->title }}</h5>
                        <p class="card-text text-muted small mb-4 text-truncate-2">
                            {{ $post->description ?? '暫無詳細說明...' }}
                        </p>

                        <!-- 代購人資訊 -->
                        <div class="d-flex align-items-center mb-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($post->user->name) }}&background=random" class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                            <small class="text-muted">{{ $post->user->name }}</small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <div class="small text-muted">
                                <i class="bi bi-calendar-event me-1"></i> {{ $post->deadline ? $post->deadline->format('m/d') : 'TBA' }} 截止
                            </div>
                            <span class="text-success fw-bold small">
                                <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i> 進行中
                            </span>
                        </div>
                    </div>
                    <!-- 點擊整張卡片進入詳情 -->
                    <a href="#" class="stretched-link"></a>
                </div>
            </div>
            @endforeach
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

        <div class="row g-3">
            @foreach($requests as $request)
            <div class="col-md-6 col-lg-3">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border position-relative hover-bg-primary">
                    <div class="mb-3 d-flex justify-content-between">
                        <span class="badge bg-light text-dark border">{{ $request->country }}</span>
                        <span class="text-danger fw-bold small">
                            預算: ${{ number_format($request->budget_total) }}
                        </span>
                    </div>
                    <h6 class="fw-bold mb-2 text-truncate">{{ $request->title }}</h6>
                    <p class="text-muted small mb-3 text-truncate-2" style="min-height: 40px;">
                        {{ $request->note ?? '沒有詳細說明...' }}
                    </p>
                    
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                             <img src="https://ui-avatars.com/api/?name={{ $request->user->name }}" class="rounded-circle me-2" width="24">
                             <small class="text-muted" style="font-size: 0.8rem">{{ $request->user->name }}</small>
                        </div>
                        <a href="#" class="btn btn-sm btn-outline-success rounded-pill stretched-link">
                            接單
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-5">
            <a href="#" class="btn btn-outline-dark rounded-pill px-5 py-2">瀏覽所有許願</a>
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