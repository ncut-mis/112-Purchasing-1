@extends('layouts.front')

@section('content')

<!-- Hero Header -->
<section class="hero-section position-relative d-flex align-items-center" style="min-height: 500px; background: linear-gradient(135deg, #e0f7fa 0%, #f3f7f5 100%);">
    <div class="container">
        <div class="row align-items-center">
           <div class="col-lg-6">
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
                            placeholder="輸入代購關鍵字（如 iPhone）"
                            value="{{ request('q') }}"
                            style="width: 300px;">
                            <select name="country" class="form-select shadow-sm" style="width: 150px;">
                            <option value="">所有國家</option>
                            @foreach(['日本', '韓國', '美國', '英國'] as $country)
                <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                    {{ $country }}
                </option>
            @endforeach
        </select>
                        <button type="submit" class="btn btn-primary-custom btn-lg shadow-sm h-100">
                            <i class="bi bi-search me-2"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-lg-6 text-center d-none d-lg-block">
                <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Shopping" class="img-fluid rounded-4 shadow-lg" style="transform: rotate(-3deg);">
            </div>
        </div>
    </div>
</section>

{{-- =====================================================================
     【熱門代購團區塊】
     修改：圖片區改為 Bootstrap Carousel 滑動式顯示所有商品圖片
     ===================================================================== --}}
<section class="py-5 bg-white">
    <div class="container">
        <div class="mb-5 text-center text-md-start">
            <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                <div style="width: 40px; height: 2px; background-color: #ef4444;"></div>
                <h6 class="text-danger fw-bold text-uppercase m-0" style="letter-spacing: 2px;">Trending Now</h6>
            </div>
            <h2 class="fw-black m-0" style="font-weight: 900; font-size: 2.5rem;">熱門代購團</h2>
            <p class="text-muted mt-2">目前社群內最受歡迎、最多人收藏的代購團。</p>
        </div>

        <div class="row g-4">
            @forelse(($hotPosts ?? collect()) as $index => $popPost)
                @php
                    // 取得該貼文所有商品的圖片 URL，過濾掉空值
                    $popImages = $popPost->products
                        ->map(fn($p) => $p->display_image_url)
                        ->filter()
                        ->values();
                @endphp

                @php
                    $scoreBadgeColors = [
                        0 => '#d4af37',
                        1 => '#9ca3af',
                        2 => '#b87333',
                    ];
                    $scoreBadgeColor = $scoreBadgeColors[$index] ?? '#ffffff';
                    $scoreTextColor = $index < 3 ? '#1f2937' : '#374151';
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden position-relative hover-lift transition">

                        {{-- 圖片區：改為 Carousel 滑動式 --}}
                        <div class="position-relative" style="height: 240px;">

                            {{-- HOT 徽章 --}}
                            <div class="position-absolute top-0 start-0 m-3" style="z-index: 10;">
                                <span class="badge bg-danger shadow-sm rounded-pill px-3 py-2">
                                    <i class="bi bi-fire me-1"></i> HOT
                                </span>
                            </div>

                            <div class="position-absolute top-0 end-0 m-3" style="z-index: 10;">
                                <span class="badge rounded-pill px-3 py-2 border shadow-sm"
                                      style="background-color: {{ $scoreBadgeColor }}; color: {{ $scoreTextColor }};">
                                    熱門分數:{{ (int) ($popPost->hot_score ?? 0) }}分
                                </span>
                            </div>

                            {{-- 國家徽章 --}}
                            <span class="position-absolute bottom-0 start-0 m-3 badge bg-white text-dark shadow-sm rounded-pill px-3" style="z-index: 10;">
                                {{ $popPost->country }}
                            </span>

                            @if($popImages->isNotEmpty())
                                <div id="carouselHot{{ $popPost->id }}" class="carousel slide h-100" data-bs-ride="carousel">
                                    <div class="carousel-inner h-100">
                                        @foreach($popImages as $imgUrl)
                                            <div class="carousel-item h-100 {{ $loop->first ? 'active' : '' }}">
                                                <img src="{{ $imgUrl }}"
                                                     class="w-100 h-100 object-fit-cover"
                                                     alt="{{ $popPost->title}} 商品圖片 {{ $loop->index + 1 }}">
                                            </div>
                                        @endforeach
                                    </div>

                                    @if($popImages->count() > 1)
                                        {{-- 指示點 --}}
                                        <div class="carousel-indicators mb-1">
                                            @foreach($popImages as $i => $url)
                                                <button type="button"
                                                        data-bs-target="#carouselHot{{ $popPost->id }}"
                                                        data-bs-slide-to="{{ $i }}"
                                                        class="{{ $i === 0 ? 'active' : '' }}"
                                                        aria-label="圖片 {{ $i + 1 }}"></button>
                                            @endforeach
                                        </div>
                                        {{-- 左右箭頭 --}}
                                        <button class="carousel-control-prev" type="button"
                                                data-bs-target="#carouselHot{{ $popPost->id }}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button"
                                                data-bs-target="#carouselHot{{ $popPost->id }}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                {{-- 無圖片佔位符 --}}
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-secondary">
                                    <i class="bi bi-image fs-1"></i>
                                </div>
                            @endif
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="fw-bold mb-3 text-truncate">{{ $popPost->title }}</h5>

                            <button
                                type="button"
                                class="btn btn-light border rounded-pill d-inline-flex align-items-center justify-content-center gap-2 fw-semibold text-secondary agent-post-toggle-btn mb-4"
                                style="min-width: 260px;"
                                data-target="hot-post-details-{{ $popPost->id }}"
                                aria-expanded="false"
                                aria-controls="hot-post-details-{{ $popPost->id }}"
                            >
                                <span>展開詳細資訊</span>
                                <i class="bi bi-chevron-down transition-icon"></i>
                            </button>

                            <div id="hot-post-details-{{ $popPost->id }}" class="agent-post-details d-none mb-4">
                                <div class="mb-3">
                                    <div class="small text-uppercase text-muted fw-bold mb-2">商品資訊（名稱 / 單價 / 目前可下單上限）</div>
                                    <div class="d-flex flex-column gap-2">
                                        @forelse($popPost->products as $product)
                                            @php
                                                $maxQuantity = (int) ($product->max_quantity ?? 0);
                                                $soldQuantity = (int) ($product->sold_quantity ?? 0);
                                                $currentMaxQuantity = max($maxQuantity - $soldQuantity, 0);
                                            @endphp
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 rounded-3 border bg-white px-3 py-2">
                                                <span class="fw-semibold text-dark">{{ $product->name }}</span>
                                                <div class="d-flex align-items-center gap-2 small text-muted">
                                                    <span class="badge rounded-pill text-bg-light border">單價：NT$ {{ number_format((float) ($product->price ?? 0), 0) }}</span>
                                                    <span class="badge rounded-pill text-bg-light border">目前可下單上限：{{ $currentMaxQuantity }}</span>
                                                </div>
                                            </div>
                                        @empty
                                            <span class="badge rounded-pill border text-dark bg-white px-3 py-2 fw-semibold">尚未建立商品明細</span>
                                        @endforelse
                                    </div>
                                </div>

                                <p class="text-dark mb-3 small" style="line-height: 1.6;">
                                    {{ \Illuminate\Support\Str::limit($popPost->description ?: '代購人尚未填寫詳細說明。', 200) }}
                                </p>

                                <div class="rounded-3 bg-light px-3 py-2 border" style="border-color: #eef1f4 !important;">
                                    <div class="d-flex align-items-center text-secondary small">
                                        <i class="bi bi-calendar-event me-2"></i>
                                        <span>代購期間：{{ optional($popPost->start_date)->format('Y/m/d') }} - {{ optional($popPost->end_date)->format('Y/m/d') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto d-flex align-items-center justify-content-between pt-3 border-top">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($popPost->user->name) }}&background=6366f1&color=fff" class="rounded-circle" width="28" height="28">
                                    <span class="small fw-bold text-gray-700">{{ $popPost->user->nickname ?? $popPost->user->name }}</span>
                                </div>
                                <span class="text-danger fw-bold" style="font-size: 12px;">
                                    <i class="bi bi-fire me-1"></i> 熱門代購團
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">目前暫無熱門團</p>
                </div>
            @endforelse
        </div>
    </div>
</section>


{{-- =====================================================================
     【最新代購團區塊】
     修改：圖片區改為 Bootstrap Carousel 滑動式顯示所有商品圖片
     ===================================================================== --}}
<section class="py-5">
    <div class="container">
        @if(session('status'))
            <div class="alert alert-success rounded-4 shadow-sm border-0 mb-4">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->has('follow_order'))
            <div class="alert alert-danger rounded-4 shadow-sm border-0 mb-4">
                {{ $errors->first('follow_order') }}
            </div>
        @endif

        @if(request()->filled('search') || request()->filled('country'))
            <div class="alert alert-info rounded-4 mb-4 border-0 shadow-sm">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    @if(request()->filled('search'))
                    <span class="badge bg-primary rounded-pill px-3 py-2">
                        <i class="bi bi-search me-1"></i>{{ request('search') }}
                    </span>
                    @endif

                    @if(request()->filled('country'))
                    <span class="badge bg-success rounded-pill px-3 py-2">
                        <i class="bi bi-geo-alt me-1"></i>{{ request('country') }}
                    </span>
                    @endif

                    <div class="ms-auto">
                        <a href="{{ route('agent.posts.search') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-x-circle me-1"></i>清除
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="text-success fw-bold text-uppercase mb-1">Agent Posts</h6>
                    <h2 class="fw-bold">最新代購團</h2>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <a href="{{ route('store.index') }}" class="text-decoration-none text-muted">
                            查看全部 <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4">
            @forelse($agentPosts as $agentPost)
                @php
                    // 取得該貼文所有商品的圖片 URL，過濾掉空值
                    $latestImages = $agentPost->products
                        ->map(fn($p) => $p->display_image_url)
                        ->filter()
                        ->values();

                    $isFavorited = in_array((int) $agentPost->id, $favoritedAgentPostIds ?? [], true);
                    $isOwner = auth()->check() && (int) auth()->id() === (int) $agentPost->user_id;
                @endphp

                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                        {{-- 圖片區：改為 Carousel 滑動式 --}}
                        <div class="position-relative" style="height: 210px;">

                            {{-- 地區徽章 --}}
                            <span class="position-absolute top-0 start-0 m-3 badge rounded-pill bg-dark-subtle text-dark" style="z-index: 10;">
                                {{ $agentPost->country }}{{ $agentPost->city ? '・' . $agentPost->city : '' }}
                            </span>

                            {{-- 狀態徽章 --}}
                            <span class="position-absolute top-0 end-0 m-3 badge rounded-pill bg-success" style="z-index: 10;">
                                {{ $agentPost->status === 'open' ? '接單中' : $agentPost->status }}
                            </span>

                            @if($latestImages->isNotEmpty())
                                <div id="carouselLatest{{ $agentPost->id }}" class="carousel slide h-100" data-bs-ride="carousel">
                                    <div class="carousel-inner h-100">
                                        @foreach($latestImages as $imgUrl)
                                            <div class="carousel-item h-100 {{ $loop->first ? 'active' : '' }}">
                                                <img src="{{ $imgUrl }}"
                                                     class="w-100 h-100 object-fit-cover"
                                                     alt="{{ $agentPost->title }} 商品圖片 {{ $loop->index + 1 }}">
                                            </div>
                                        @endforeach
                                    </div>

                                    @if($latestImages->count() > 1)
                                        {{-- 指示點 --}}
                                        <div class="carousel-indicators mb-1">
                                            @foreach($latestImages as $i => $url)
                                                <button type="button"
                                                        data-bs-target="#carouselLatest{{ $agentPost->id }}"
                                                        data-bs-slide-to="{{ $i }}"
                                                        class="{{ $i === 0 ? 'active' : '' }}"
                                                        aria-label="圖片 {{ $i + 1 }}"></button>
                                            @endforeach
                                        </div>
                                        {{-- 左右箭頭 --}}
                                        <button class="carousel-control-prev" type="button"
                                                data-bs-target="#carouselLatest{{ $agentPost->id }}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button"
                                                data-bs-target="#carouselLatest{{ $agentPost->id }}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    @endif
                                </div>
                            @elseif($agentPost->cover_image)
                                {{-- fallback：貼文封面圖 --}}
                                <img src="{{ asset('storage/' . $agentPost->cover_image) }}"
                                     alt="{{ $agentPost->title }}"
                                     class="w-100 h-100 object-fit-cover">
                            @else
                                {{-- 無圖片佔位符 --}}
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-secondary">
                                    <i class="bi bi-image fs-1"></i>
                                </div>
                            @endif
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                <h5 class="card-title fw-bold mb-0 flex-grow-1">{{ $agentPost->title }}</h5>
                                <div class="d-flex gap-2">
                                    {{-- 檢舉按鈕 --}}
                                    <button
                                        type="button"
                                        class="agent-post-report-btn rounded-circle d-inline-flex align-items-center justify-content-center border-0 shadow-sm flex-shrink-0"
                                        style="width: 2.25rem; height: 2.25rem; background: #f3f4f6; transition: background-color 0.2s ease, transform 0.2s ease; {{ $isOwner ? 'opacity:0.5;cursor:not-allowed;' : '' }}"
                                        aria-label="{{ $isOwner ? '不能檢舉自己的代購團' : '檢舉代購團' }}"
                                        data-agent-post-id="{{ $agentPost->id }}"
                                        @disabled($isOwner)
                                        title="{{ $isOwner ? '不能檢舉自己的代購團' : '檢舉代購團' }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ef4444" style="width: 1.2rem; height: 1.2rem; filter: drop-shadow(0 0 1px rgba(0,0,0,0.1));">
                                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                                        </svg>
                                    </button>
                                    {{-- 收藏按鈕 --}}
                                    <button
                                        type="button"
                                        class="favorite-toggle rounded-circle d-inline-flex align-items-center justify-content-center border-0 shadow-sm flex-shrink-0"
                                        style="width: 2.25rem; height: 2.25rem; background: {{ $isFavorited ? '#fce7f3' : '#f3f4f6' }}; color: {{ $isFavorited ? '#ec4899' : '#9ca3af' }}; transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease; {{ $isOwner ? 'opacity:0.5;cursor:not-allowed;' : '' }}"
                                        aria-label="{{ $isOwner ? '不能收藏自己的代購團' : '收藏代購團' }}"
                                        aria-pressed="{{ $isFavorited ? 'true' : 'false' }}"
                                        data-agent-post-id="{{ $agentPost->id }}"
                                        @disabled($isOwner)
                                        title="{{ $isOwner ? '不能收藏自己的代購團' : '收藏代購團' }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 1.1rem; height: 1.1rem;">
                                            <path d="M12.001 4.529c2.349-2.532 6.15-2.533 8.498-.001 2.41 2.6 2.41 6.815 0 9.416l-7.66 8.266a1.14 1.14 0 0 1-1.677 0l-7.66-8.266c-2.41-2.601-2.41-6.817 0-9.416 2.348-2.532 6.149-2.531 8.499.001Z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- 展開詳細資訊按鈕 --}}
                            <button
                                type="button"
                                class="btn btn-light border rounded-pill d-inline-flex align-items-center justify-content-center gap-2 fw-semibold text-secondary agent-post-toggle-btn mb-4"
                                data-target="agent-post-details-{{ $agentPost->id }}"
                                aria-expanded="false"
                                aria-controls="agent-post-details-{{ $agentPost->id }}"
                            >
                                <span>展開詳細資訊</span>
                                <i class="bi bi-chevron-down transition-icon"></i>
                            </button>

                            <div id="agent-post-details-{{ $agentPost->id }}" class="agent-post-details d-none mb-4">
                                <div class="mb-3">
                                    <div class="small text-uppercase text-muted fw-bold mb-2">商品資訊（名稱 / 單價 / 目前可下單上限）</div>
                                    <div class="d-flex flex-column gap-2">
                                        @forelse($agentPost->products as $product)
                                            @php
                                                $maxQuantity = (int) ($product->max_quantity ?? 0);
                                                $soldQuantity = (int) ($product->sold_quantity ?? 0);
                                                $currentMaxQuantity = max($maxQuantity - $soldQuantity, 0);
                                            @endphp
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 rounded-3 border bg-white px-3 py-2">
                                                <span class="fw-semibold text-dark">{{ $product->name }}</span>
                                                <div class="d-flex align-items-center gap-2 small text-muted">
                                                    <span class="badge rounded-pill text-bg-light border">單價：NT$ {{ number_format((float) ($product->price ?? 0), 0) }}</span>
                                                    <span class="badge rounded-pill text-bg-light border">目前可下單上限：{{ $currentMaxQuantity }}</span>
                                                </div>
                                            </div>
                                        @empty
                                            <span class="badge rounded-pill border text-dark bg-white px-3 py-2 fw-semibold">尚未建立商品明細</span>
                                        @endforelse
                                    </div>
                                </div>

                                <p class="text-dark mb-3 small" style="line-height: 1.6;">
                                    {{ \Illuminate\Support\Str::limit($agentPost->description ?: '代購人尚未填寫詳細說明。', 200) }}
                                </p>

                                <div class="rounded-3 bg-light px-3 py-2 border" style="border-color: #eef1f4 !important;">
                                    <div class="d-flex align-items-center text-secondary small">
                                        <i class="bi bi-calendar-event me-2"></i>
                                        <span>代購期間：{{ optional($agentPost->start_date)->format('Y/m/d') }} - {{ optional($agentPost->end_date)->format('Y/m/d') }}</span>
                                    </div>
                                </div>
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
                                        <button class="btn btn-sm rounded-pill px-3 btn-secondary disabled">無法跟單</button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-primary-custom rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#followOrderModal-{{ $agentPost->id }}">我要跟單</button>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary-custom rounded-pill px-3">
                                        我要跟單
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 跟單 Modal --}}
                <div class="modal fade" id="followOrderModal-{{ $agentPost->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header border-0 bg-light py-3 px-4">
                                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-cart-plus me-2 text-primary"></i>確認跟單商品</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <form action="{{ route('orders.store', $agentPost) }}" method="POST" class="follow-order-form">
                                @csrf
                                <div class="modal-body p-4">
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded-4 h-100 bg-light-subtle">
                                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">銷售期間</label>
                                                <div class="text-dark fw-bold">
                                                    {{ optional($agentPost->start_date)->format('Y/m/d') }} <span class="mx-1 text-muted">至</span> {{ optional($agentPost->end_date)->format('Y/m/d') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded-4 h-100 bg-light-subtle">
                                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">描述訊息</label>
                                                <div class="text-muted small text-truncate">
                                                    {{ $agentPost->description ?: '無詳細說明。' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead class="bg-light">
                                                <tr class="small text-muted border-0">
                                                    <th class="border-0 ps-0" style="width: 70px;">圖片</th>
                                                    <th class="border-0">商品名稱</th>
                                                    <th class="border-0 text-center">可下單數量</th>
                                                    <th class="border-0 text-center">單價</th>
                                                    <th class="border-0 text-center" style="width: 140px;">數量</th>
                                                    <th class="border-0 text-end pe-0">小計</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($agentPost->products as $product)
                                                    @php
                                                        $max = $product->max_quantity ?? 0;
                                                        $sold = $product->sold_quantity ?? 0;
                                                        $remaining = $max - $sold;
                                                    @endphp
                                                    <tr class="product-row" data-price="{{ $product->price }}">
                                                        <td class="ps-0">
                                                            <img src="{{ $product->display_image_url ?? 'https://via.placeholder.com/60' }}"
                                                                 class="rounded-3 object-fit-cover shadow-sm" width="55" height="55">
                                                        </td>
                                                        <td>
                                                            <div class="fw-bold text-dark mb-0">{{ $product->name }}</div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $remaining > 0 ? 'bg-info-subtle text-info' : 'bg-danger-subtle text-danger' }} rounded-pill">
                                                                {{ $remaining > 0 ? '還有 ' . $remaining : '已售罄' }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center fw-semibold text-muted">
                                                            ${{ number_format($product->price) }}
                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-sm border rounded-pill overflow-hidden bg-white mx-auto" style="max-width: 110px;">
                                                                <button class="btn btn-link text-decoration-none border-0 px-2 qty-minus" type="button" {{ $remaining <= 0 ? 'disabled' : '' }}>
                                                                    <i class="bi bi-dash-lg"></i>
                                                                </button>
                                                                <input type="number" name="products[{{ $product->id }}][quantity]"
                                                                       class="form-control border-0 text-center bg-transparent qty-input"
                                                                       value="0"
                                                                       min="0"
                                                                       max="{{ $remaining }}"
                                                                       {{ $remaining <= 0 ? 'disabled' : '' }}
                                                                       style="box-shadow: none;">
                                                                <button class="btn btn-link text-decoration-none border-0 px-2 qty-plus" type="button" {{ $remaining <= 0 ? 'disabled' : '' }}>
                                                                    <i class="bi bi-plus-lg"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                        <td class="text-end pe-0 fw-bold text-primary subtotal">
                                                            $0
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="modal-footer border-0 p-4 pt-0 flex-column align-items-end">
                                    <div class="d-flex align-items-baseline mb-4">
                                        <span class="text-muted me-3">總計金額：</span>
                                        <span class="h3 fw-bold text-success mb-0">NT$ <span class="total-amount">0</span></span>
                                    </div>
                                    <div class="d-flex gap-2 w-100">
                                        <button type="button" class="btn btn-light rounded-pill flex-grow-1 py-2 fw-bold" data-bs-dismiss="modal">再逛逛</button>
                                        <button type="submit" class="btn btn-primary-custom rounded-pill flex-grow-1 py-2 fw-bold shadow follow-order-submit-btn">確認結帳</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- 檢舉貼文 Modal --}}
                <div class="modal fade" id="reportAgentPostModal-{{ $agentPost->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header border-0 bg-light py-3 px-4">
                                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>檢舉代購團</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <form class="report-form" data-target-type="agent_post" data-target-id="{{ $agentPost->id }}">
                                @csrf
                                <div class="modal-body p-4">
                                    <div class="mb-4">
                                        <label for="reportType-{{ $agentPost->id }}" class="form-label fw-bold text-dark mb-2">
                                            <i class="bi bi-list-check text-danger me-2"></i>檢舉違規類型 <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select rounded-3 border shadow-sm" id="reportType-{{ $agentPost->id }}" name="report_type" required>
                                            <option value="" selected disabled>請選擇違規類型</option>
                                            <option value="false_info">虛假信息</option>
                                            <option value="fraud">詐騙嫌疑</option>
                                            <option value="prohibited_items">違禁品</option>
                                            <option value="copyright">侵權</option>
                                            <option value="harassment">騷擾或威脅</option>
                                            <option value="spam">垃圾訊息</option>
                                            <option value="other">其他</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="reportReason-{{ $agentPost->id }}" class="form-label fw-bold text-dark mb-2">
                                            <i class="bi bi-chat-dots text-danger me-2"></i>檢舉原因 <span class="text-danger">*</span>
                                        </label>
                                        <textarea
                                            class="form-control rounded-3 border shadow-sm"
                                            id="reportReason-{{ $agentPost->id }}"
                                            name="reason"
                                            rows="4"
                                            placeholder="請詳細描述檢舉原因（最多500字）"
                                            maxlength="500"
                                            required
                                            style="resize: none;"></textarea>
                                        <div class="form-text mt-1">
                                            <span class="char-count">0</span>/500 字
                                        </div>
                                    </div>

                                    <div class="alert alert-info border-0 rounded-3 mb-0" role="alert">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <small>感謝您的檢舉，我們會認真審查您提供的信息，維護平台安全。</small>
                                    </div>
                                </div>

                                <div class="modal-footer border-0 p-4 pt-0 flex-column align-items-stretch">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-light rounded-pill flex-grow-1 py-2 fw-bold" data-bs-dismiss="modal">取消</button>
                                        <button type="submit" class="btn btn-danger rounded-pill flex-grow-1 py-2 fw-bold shadow report-submit-btn">提交檢舉</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            @empty
                @if(request()->has('search'))
                    <div class="col-12">
                        <div class="text-center py-12 bg-light rounded-4">
                            <i class="bi bi-search display-1 text-muted mb-4 opacity-50"></i>
                            <h3 class="text-muted mb-3">沒有找到符合條件的代購團</h3>
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
                            目前尚無最新代購連線，歡迎代購人前往會員專區建立團。
                        </div>
                    </div>
                @endif
            @endforelse
        </div>
    </div>
</section>


<!-- Newsletter / CTA Section -->
<section class="py-5">
    <div class="container">
        <div class="p-5 rounded-4 text-white text-center shadow-lg" style="background: linear-gradient(45deg, #5A9E8E, #3b7d6e);">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="fw-black mb-3">準備好開始代購了嗎？</h2>
                    <p class="lead mb-4 opacity-75">無論您是想買東西，還是即將出國想順便賺旅費，這裡都是您的最佳選擇。</p>
                    <a href="{{ route('register') }}" class="btn btn-light text-success btn-lg rounded-pill px-5 fw-bold shadow-sm transition hover-scale">
                        免費註冊會員
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .fw-black { font-weight: 900; }
    .hover-shadow:hover { transform: translateY(-5px); box-shadow: 0 1rem 3rem rgba(0,0,0,.1) !important; }
    .transition { transition: all 0.3s ease; }
    .hover-scale:hover { transform: scale(1.05); }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    /* Carousel 箭頭陰影加強，在淺色圖片上更清楚 */
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        filter: drop-shadow(0 0 3px rgba(0,0,0,0.7));
    }
    /* 指示點縮小以配合卡片高度 */
    .carousel-indicators [data-bs-slide-to] {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }
</style>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = '{{ csrf_token() }}';
        const favoriteToggleUrl = '{{ route('favorite.toggle') }}';
        const reportStoreUrl = '{{ route('reports.store') }}';
        const loginUrl = '{{ route('login') }}';
        const isAuthenticated = @json(auth()->check());

        // 1. 展開/收起詳細資訊
        document.querySelectorAll('.agent-post-toggle-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const card = button.closest('.card');
                const details = card ? card.querySelector('.agent-post-details') : null;
                if (!details) return;

                const isHidden = details.classList.contains('d-none');
                details.classList.toggle('d-none', !isHidden);
                button.setAttribute('aria-expanded', isHidden ? 'true' : 'false');

                const label = button.querySelector('span');
                const icon = button.querySelector('.transition-icon');
                if (label) label.textContent = isHidden ? '收起詳細資訊' : '展開詳細資訊';
                if (icon) {
                    icon.classList.toggle('bi-chevron-down', !isHidden);
                    icon.classList.toggle('bi-chevron-up', isHidden);
                }
            });
        });

        // 2. 收藏按鈕邏輯
        document.querySelectorAll('.favorite-toggle').forEach(function (button) {
            button.addEventListener('click', async function () {
                if (!isAuthenticated) { window.location.href = loginUrl; return; }
                const agentPostId = button.dataset.agentPostId;
                if (!agentPostId || button.disabled) return;

                button.disabled = true;
                try {
                    const response = await fetch(favoriteToggleUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ type: 'agent_post', id: agentPostId }),
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || '收藏失敗');
                    const isAdded = data.status === 'added';
                    button.style.background = isAdded ? '#fce7f3' : '#f3f4f6';
                    button.style.color = isAdded ? '#ec4899' : '#9ca3af';
                } catch (error) {
                    alert(error.message || '操作失敗，請稍後再試。');
                } finally {
                    button.disabled = false;
                }
            });
        });

        // 3. 檢舉按鈕邏輯
        document.querySelectorAll('.agent-post-report-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                if (button.disabled) return;
                const agentPostId = button.dataset.agentPostId;
                if (!isAuthenticated) { window.location.href = loginUrl; return; }
                const modalElement = document.getElementById('reportAgentPostModal-' + agentPostId);
                if (modalElement) new bootstrap.Modal(modalElement).show();
            });
        });

        // 4. 檢舉原因字符計數
        document.querySelectorAll('textarea[name="reason"]').forEach(function (textarea) {
            textarea.addEventListener('input', function () {
                const span = this.parentElement.querySelector('.char-count');
                if (span) span.textContent = this.value.length;
            });
        });

        // 5. 檢舉表單提交
        document.querySelectorAll('.report-form').forEach(function (form) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const reportType = form.querySelector('select[name="report_type"]').value;
                const reason = form.querySelector('textarea[name="reason"]').value;
                const targetType = form.dataset.targetType;
                const targetId = form.dataset.targetId;
                const submitBtn = form.querySelector('.report-submit-btn');

                if (!reportType) { alert('請選擇違規類型'); return; }
                if (!reason.trim()) { alert('請輸入檢舉原因'); return; }

                submitBtn.disabled = true;
                try {
                    const response = await fetch(reportStoreUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ target_type: targetType, target_id: targetId, report_type: reportType, reason: reason.trim() }),
                    });
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || '檢舉提交失敗');
                    alert(data.message || '感謝您的檢舉！我們會盡快審查您的報告。');
                    const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
                    if (modal) modal.hide();
                    form.reset();
                    form.querySelectorAll('.char-count').forEach(el => el.textContent = '0');
                } catch (error) {
                    alert(error.message || '檢舉提交失敗');
                } finally {
                    submitBtn.disabled = false;
                }
            });
        });

        // 6. Modal 數量與金額即時計算
        document.querySelectorAll('[id^="followOrderModal-"]').forEach(modal => {
            const form = modal.querySelector('.follow-order-form');
            const submitBtn = modal.querySelector('.follow-order-submit-btn');
            const totalAmountNode = modal.querySelector('.total-amount');

            if (!form || !submitBtn || !totalAmountNode) return;

            const updateTotals = () => {
                let grandTotal = 0;
                let totalQuantity = 0;
                modal.querySelectorAll('.product-row').forEach(row => {
                    const price = parseFloat(row.dataset.price) || 0;
                    const qty = parseInt(row.querySelector('.qty-input').value) || 0;
                    const subtotal = price * qty;
                    row.querySelector('.subtotal').textContent = '$' + subtotal.toLocaleString();
                    grandTotal += subtotal;
                    totalQuantity += qty;
                });
                totalAmountNode.textContent = grandTotal.toLocaleString();
                submitBtn.disabled = totalQuantity < 1;
            };

            modal.addEventListener('click', (e) => {
                const plusBtn = e.target.closest('.qty-plus');
                const minusBtn = e.target.closest('.qty-minus');

                if (plusBtn) {
                    const input = plusBtn.closest('.input-group').querySelector('.qty-input');
                    const currentVal = parseInt(input.value) || 0;
                    const maxVal = parseInt(input.getAttribute('max')) || 0;
                    if (currentVal < maxVal) { input.value = currentVal + 1; updateTotals(); }
                    else alert('已達該商品最大可下單數量！');
                }
                if (minusBtn) {
                    const input = minusBtn.closest('.input-group').querySelector('.qty-input');
                    if (parseInt(input.value) > 0) { input.value = parseInt(input.value) - 1; updateTotals(); }
                }
            });

            modal.querySelectorAll('.qty-input').forEach(input => {
                input.addEventListener('change', () => {
                    const maxVal = parseInt(input.getAttribute('max')) || 0;
                    let val = parseInt(input.value) || 0;
                    if (val > maxVal) { alert('不能超過可下單數量：' + maxVal); input.value = maxVal; }
                    if (val < 0) input.value = 0;
                    updateTotals();
                });
            });

            form.addEventListener('submit', (event) => {
                const totalQuantity = Array.from(modal.querySelectorAll('.qty-input'))
                    .reduce((sum, input) => sum + (parseInt(input.value) || 0), 0);
                if (totalQuantity < 1) {
                    event.preventDefault();
                    alert('請至少選擇一項商品數量後再確認結帳。');
                    return;
                }
                submitBtn.disabled = true;
                submitBtn.textContent = '建立訂單中...';
            });

            updateTotals();
        });
    });
</script>

<style>
    .btn-primary-custom { background-color: #5A9E8E; border-color: #5A9E8E; color: white; }
    .btn-primary-custom:hover { background-color: #4a8376; color: white; }
    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }
    .bg-light-subtle { background-color: #f8fafc !important; }
</style>
@endpush