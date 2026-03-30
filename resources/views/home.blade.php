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
                            placeholder="輸入代購關鍵字（如 iPhone）" 
                            value="{{ request('q') }}" 
                            style="width: 300px;">
                            <select name="country" class="form-select shadow-sm" style="width: 150px;">
                            <option value="">所有國家</option>
                            @foreach(['日本', '韓國', '美國', '歐洲', '澳洲', '其他'] as $country)
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
                <!-- 這裡使用假圖，實際上可以放一張漂亮的插畫 -->
                <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Shopping" class="img-fluid rounded-4 shadow-lg" style="transform: rotate(-3deg);">
            </div>
        </div>
    </div>
</section>

<!-- Agent Posts Section (最新代購連線) -->
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
                    {{-- 商品搜尋標籤 --}}
                    @if(request()->filled('search'))
                    <span class="badge bg-primary rounded-pill px-3 py-2">
                        <i class="bi bi-search me-1"></i>{{ request('search') }}
                    </span>
                    @endif
                    
                    {{-- 國家篩選標籤 --}}
                    @if(request()->filled('country'))
                    <span class="badge bg-success rounded-pill px-3 py-2">
                        <i class="bi bi-geo-alt me-1"></i>{{ request('country') }}
                    </span>
                    @endif
                    
                    {{-- 清除按鈕 --}}
                    <div class="ms-auto">
                        <a href="{{ route('agent.posts.search') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-x-circle me-1"></i>清除
                        </a>
                    </div>
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
                      <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="position-relative" style="height: 210px;">
                            @php
                               $firstProduct = $agentPost->products->first();
                                $firstProductImageUrl = optional($firstProduct)->display_image_url;
                            @endphp
                            @if($firstProductImageUrl)
                                <img src="{{ $firstProductImageUrl }}" alt="{{ $agentPost->title }} 商品圖片" class="w-100 h-100 object-fit-cover">
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
                             <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                <h5 class="card-title fw-bold mb-0 flex-grow-1">{{ $agentPost->title }}</h5>
                                @php
                                    $isFavorited = in_array((int) $agentPost->id, $favoritedAgentPostIds ?? [], true);
                                    $isOwner = auth()->check() && (int) auth()->id() === (int) $agentPost->user_id;
                                @endphp
                                <button
                                    type="button"
                                    class="favorite-toggle rounded-circle d-inline-flex align-items-center justify-content-center border-0 shadow-sm flex-shrink-0"
                                    style="width: 2.25rem; height: 2.25rem; background: {{ $isFavorited ? '#fce7f3' : '#f3f4f6' }}; color: {{ $isFavorited ? '#ec4899' : '#9ca3af' }}; transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease; {{ $isOwner ? 'opacity:0.5;cursor:not-allowed;' : '' }}"
                                    aria-label="{{ $isOwner ? '不能收藏自己的代購貼文' : '收藏代購貼文' }}"
                                    aria-pressed="{{ $isFavorited ? 'true' : 'false' }}"
                                    data-agent-post-id="{{ $agentPost->id }}"
                                    @disabled($isOwner)
                                    title="{{ $isOwner ? '不能收藏自己的代購貼文' : '收藏代購貼文' }}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5" style="width: 1.1rem; height: 1.1rem;">
                                        <path d="M12.001 4.529c2.349-2.532 6.15-2.533 8.498-.001 2.41 2.6 2.41 6.815 0 9.416l-7.66 8.266a1.14 1.14 0 0 1-1.677 0l-7.66-8.266c-2.41-2.601-2.41-6.817 0-9.416 2.348-2.532 6.149-2.531 8.499.001Z"/>
                                    </svg>
                                </button>
                            </div>

                            <button
                                type="button"
                                class="btn btn-light border rounded-pill d-inline-flex align-items-center justify-content-center gap-2 fw-semibold text-secondary agent-post-toggle-btn"
                                data-target="agent-post-details-{{ $agentPost->id }}"
                                aria-expanded="false"
                                aria-controls="agent-post-details-{{ $agentPost->id }}"
                            >
                                <span>展開詳細資訊</span>
                                <i class="bi bi-chevron-down transition-icon"></i>
                            </button>

                            <div id="agent-post-details-{{ $agentPost->id }}" class="agent-post-details d-none mt-4 pt-2">
                                <div class="border-top pt-4">
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

                                    <p class="text-dark mb-4" style="font-size: 1rem; line-height: 1.75;">
                                        {{ \Illuminate\Support\Str::limit($agentPost->description ?: '代購人尚未填寫詳細說明。', 60) }}
                                    </p>

                                    <div class="rounded-4 bg-light px-4 py-3 mb-4 border" style="border-color: #eef1f4 !important;">
                                        <div class="d-flex align-items-center text-secondary" style="font-size: 1rem;">
                                            <i class="bi bi-calendar-event me-3"></i>
                                            <span>代購期間：{{ optional($agentPost->start_date)->format('Y/m/d') }} - {{ optional($agentPost->end_date)->format('Y/m/d') }}</span>
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
                                      {{-- 判斷登入狀態與身份 --}}
                                        @auth
                                            @if((int) auth()->id() === (int) $agentPost->user_id)
                                                <button class="btn btn-sm rounded-pill px-3 btn-secondary disabled">無法跟單</button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-primary-custom rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#followOrderModal-{{ $agentPost->id }}">我要跟單</button>
                                            @endif
                                        @else
                                            {{-- 未登入時：改為超連結跳轉 --}}
                                            <a href="{{ route('login') }}" class="btn btn-sm btn-primary-custom rounded-pill px-3">
                                                我要跟單
                                            </a>
                                        @endauth
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
            // 計算真正的剩餘數量：最大數量 - 已售數量
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
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = '{{ csrf_token() }}';
        const favoriteToggleUrl = '{{ route('favorite.toggle') }}';
        const loginUrl = '{{ route('login') }}';
        const isAuthenticated = @json(auth()->check());

        // 1. 展開/收起詳細資訊
        document.querySelectorAll('.agent-post-toggle-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = button.dataset.target;
                const details = document.getElementById(targetId);
                if (!details) return;

                const isHidden = details.classList.contains('d-none');
                details.classList.toggle('d-none', !isHidden);
                
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
                     if (!response.ok) {
                        throw new Error(data.message || '收藏失敗');
                    }
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

        // 3. Modal 數量與金額即時計算 (優化邏輯)
        document.querySelectorAll('.modal').forEach(modal => {
            const form = modal.querySelector('.follow-order-form');
            const submitBtn = modal.querySelector('.follow-order-submit-btn');

            const updateTotals = () => {
                let grandTotal = 0;
                let totalQuantity = 0;
                modal.querySelectorAll('.product-row').forEach(row => {
                    const price = parseFloat(row.dataset.price) || 0;
                    const qtyInput = row.querySelector('.qty-input');
                    const qty = parseInt(qtyInput.value) || 0;
                    const subtotal = price * qty;
                    row.querySelector('.subtotal').textContent = '$' + subtotal.toLocaleString();
                    grandTotal += subtotal;
                    totalQuantity += qty;
                });
                modal.querySelector('.total-amount').textContent = grandTotal.toLocaleString();
                if (submitBtn) {
                    submitBtn.disabled = totalQuantity < 1;
                }
            };

    modal.addEventListener('click', (e) => {
    const plusBtn = e.target.closest('.qty-plus');
    const minusBtn = e.target.closest('.qty-minus');
    
    if (plusBtn) {
        const input = plusBtn.closest('.input-group').querySelector('.qty-input');
        const currentVal = parseInt(input.value) || 0;
        const maxVal = parseInt(input.getAttribute('max')) || 0;

        // 檢查是否超過最大可下單數量
        if (currentVal < maxVal) {
            input.value = currentVal + 1;
            updateTotals();
        } else {
            alert('已達該商品最大可下單數量！');
        }
    }
    
    if (minusBtn) {
        const input = minusBtn.closest('.input-group').querySelector('.qty-input');
        if (parseInt(input.value) > 0) {
            input.value = parseInt(input.value) - 1;
            updateTotals();
        }
    }
});

            modal.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('change', () => {
        const maxVal = parseInt(input.getAttribute('max')) || 0;
        let currentVal = parseInt(input.value) || 0;

        if (currentVal > maxVal) {
            alert('不能超過可下單數量：' + maxVal);
            input.value = maxVal;
        }
        if (currentVal < 0) input.value = 0;
        updateTotals();
    });
});

            form?.addEventListener('submit', (event) => {
                const totalQuantity = Array.from(modal.querySelectorAll('.qty-input'))
                    .reduce((sum, input) => sum + (parseInt(input.value) || 0), 0);

                if (totalQuantity < 1) {
                    event.preventDefault();
                    alert('請至少選擇一項商品數量後再確認結帳。');
                    return;
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = '建立訂單中...';
                }
            });

            updateTotals();
        });
    });
</script>

<style>
    /* 全域按鈕與表單樣式優化 */
    .btn-primary-custom {
        background-color: #5A9E8E;
        border-color: #5A9E8E;
        color: white;
    }
    .btn-primary-custom:hover {
        background-color: #4a8376;
        color: white;
    }
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
    .bg-light-subtle {
        background-color: #f8fafc !important;
    }
    .ls-1 { letter-spacing: 1px; }
</style>
@endpush