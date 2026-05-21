<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        .store-container { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 40px 20px; 
        }
        
        .search-wrapper {
            background: #fff;
            border-radius: 50px;
            padding: 10px 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid #f0f0f0;
            width: 100%;
        }

        .search-input { 
            border: none !important; 
            box-shadow: none !important; 
            padding-left: 15px;
            font-size: 1.05rem; 
        }

        .search-select { 
            border: none !important; 
            box-shadow: none !important; 
            border-left: 1px solid #eee !important; 
            border-radius: 0 !important; 
            cursor: pointer;
            padding-left: 20px !important;
            padding-right: 40px !important;
            color: #666;
        }

        .btn-search-submit {
            background-color: #5A9E8E;
            border-radius: 50px;
            padding: 10px 25px;
            color: white;
            border: none;
            transition: 0.3s;
            white-space: nowrap; 
            min-width: fit-content; 
            flex-shrink: 0; 
        }
        .btn-search-submit:hover { background-color: #4a8376; transform: scale(1.02); }
        
        .post-card { 
            border: none; 
            border-radius: 20px; 
            background: #fff; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            overflow: hidden; 
            height: 100%; 
            transition: transform 0.3s ease; 
            cursor: pointer;
        }
        .post-card:hover { transform: translateY(-5px); }

        /* 卡片上方的標籤：提高層級確保在圖片之上 */
        .badge-country { position: absolute; top: 15px; left: 15px; background: rgba(255,255,255,0.9); color: #333; font-weight: bold; font-size: 0.75rem; padding: 5px 12px; border-radius: 50px; z-index: 10; box-shadow: 0 2px 5px rgba(0,0,0,0.1);}
        .badge-status { position: absolute; top: 15px; right: 15px; background: #198754; color: #fff; font-size: 0.75rem; padding: 5px 12px; border-radius: 50px; z-index: 10; box-shadow: 0 2px 5px rgba(0,0,0,0.1);}

        /* 卡片圖片 Slider 容器與圖片 */
        .card-slider-container {
            height: 200px; /* 稍微加高一點讓卡片更好看 */
            width: 100%;
            position: relative;
            background-color: #f8f9fa;
        }
        .card-slider-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            object-position: center;
        }

        /* Modal 圖片 Slider 的容器與圖片 */
        .modal-slider-container {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            height: 100%;
            min-height: 350px; 
            overflow: hidden;
            background-color: #f8f9fa;
            position: relative;
        }
        .modal-slider-name {
            position: absolute;
            top: 16px;
            left: 16px;
            right: 16px;
            z-index: 15;
            background: rgba(255, 255, 255, 0.92);
            border-radius: 999px;
            padding: 0.65rem 1rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .modal-slider-name-text {
            display: block;
            font-weight: 700;
            color: #1f2937;
            font-size: 0.95rem;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .modal-slider-img {
            width: 100%;
            height: 350px;
            object-fit: cover; 
            object-position: center; 
        }

        /* 無圖片時的通用 Placeholder */
        .img-placeholder {
            background-color: #f8f9fa;
            width: 100%;
            height: 100%;
            min-height: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
        }

        /* 讓左右切換箭頭更顯眼 */
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            filter: drop-shadow(0 0 4px rgba(0,0,0,0.8));
        }

        .qty-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #888;
            padding: 0 8px;
            cursor: pointer;
            transition: 0.2s;
        }
        .qty-btn:hover { color: #5A9E8E; }
        .custom-input {
            background-color: #fff !important;
            border: 1px solid #e2e8f0;
        }
        .custom-table th {
            font-weight: 600;
            color: #4a5568;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 15px;
        }
        .custom-table td {
            border-bottom: 1px solid #f7fafc;
            padding: 15px 0;
            color: #4a5568;
        }
        
        /* 結帳按鈕未滿足條件時的禁用狀態樣式 */
        .btn-checkout:disabled {
            background-color: #a8c7bd !important;
            border-color: #a8c7bd !important;
            cursor: not-allowed;
            opacity: 0.8;
        }
    </style>

    <div class="store-container">
        
        <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between mb-5 gap-4">
            <div class="text-nowrap">
                <h6 class="text-success fw-bold text-uppercase mb-1" style="letter-spacing: 2px; font-size: 0.8rem;">All Agent Posts</h6>
                <h2 class="fw-bold mb-0">全部代購團</h2>
            </div>
            
            <div class="flex-grow-1 ms-lg-5" style="max-width: 850px; width: 100%;">
                <form action="{{ route('store.index') }}" method="GET">
                    <div class="search-wrapper d-flex align-items-center">
                        <i class="bi bi-search text-muted ms-3"></i>
                        <input type="text" name="search" class="form-control search-input" 
                               placeholder="搜尋商品關鍵字..." value="{{ request('search') }}">
                        
                        <select name="country" class="form-select search-select w-auto" onchange="this.form.submit()">
                            <option value="">所有國家</option>
                            @foreach(['日本', '韓國', '美國', '歐洲', '澳洲', '其他'] as $country)
                                <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                                    {{ $country }}
                                </option>
                            @endforeach
                        </select>
                        
                        <button type="submit" class="btn-search-submit px-10">
                            搜尋
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(request()->filled('search') || request()->filled('country'))
            <div class="d-flex align-items-center gap-2 mb-4">
                <span class="text-muted small">篩選條件：</span>
                @if(request('search'))
                    <span class="filter-badge"><i class="bi bi-tag me-1"></i>{{ request('search') }}</span>
                @endif
                @if(request('country'))
                    <span class="filter-badge"><i class="bi bi-geo-alt me-1"></i>{{ request('country') }}</span>
                @endif
                <a href="{{ route('store.index') }}" class="btn btn-link btn-sm text-decoration-none text-danger ms-2">清除全部</a>
            </div>
        @endif

        <div class="row g-4">
            @forelse($posts as $post) 
                <div class="col-12 col-sm-6 col-lg-4">
                    {{-- 卡片主體 --}}
                    <div class="card post-card" data-bs-toggle="modal" data-bs-target="#orderModal{{ $post->id }}">
                        
                        <div class="card-slider-container position-relative">
                            <span class="badge-country">{{ $post->country }}</span>
                            <span class="badge-status">接單中</span>

                            @php
                                // 抓取這則貼文下的所有商品圖片
                                $cardImages = $post->products->filter(fn($product) => $product->image_path);
                            @endphp

                            @if($cardImages->isNotEmpty())
                                {{-- 外層卡片的圖片輪播 --}}
                                <div id="carouselCard{{ $post->id }}" class="carousel slide h-100" data-bs-ride="carousel">
                                    <div class="carousel-inner h-100">
                                        @foreach($cardImages as $index => $product)
                                            <div class="carousel-item h-100 {{ $loop->first ? 'active' : '' }}">
                                                <img src="{{ route('post-product.image', ['postProduct' => $product->id, 'v' => $product->updated_at?->timestamp ?? now()->timestamp]) }}" class="card-slider-img" alt="商品圖片 {{ $index + 1 }}">
                                            </div>
                                        @endforeach
                                    </div>

                                    @if($cardImages->count() > 1)
                                        {{-- 加上 onclick="event.stopPropagation();" 防止點擊箭頭時觸發外層的 Modal --}}
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselCard{{ $post->id }}" data-bs-slide="prev" onclick="event.stopPropagation();">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselCard{{ $post->id }}" data-bs-slide="next" onclick="event.stopPropagation();">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    @endif
                                </div>
                            @else
                                {{-- 無圖片佔位符 --}}
                                <div class="img-placeholder">
                                    <i class="bi bi-image fs-1"></i>
                                </div>
                            @endif
                        </div>

                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold">{{ $post->title }}</h5>
                            <p class="text-muted small mb-0">
                                {{ \Illuminate\Support\Str::limit($post->description ?: '無詳細說明。', 60) }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 對應的跟單彈出視窗 (Modal) --}}
                <div class="modal fade" id="orderModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        
                        <form action="#" method="POST" class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 12px;">
                            @csrf
                            <input type="hidden" name="agent_post_id" value="{{ $post->id }}">

                            <div style="background-color: #63a388; height: 45px; width: 100%;">
                                     <h5 class="modal-title fw-bold text-white p-md-2"><i class="bi bi-cart-plus me-2 text-white"></i>確認跟單商品</h5>
                            </div>
                            <div class="modal-body p-4 p-md-8 pt-4">

                            
                                <div class="row mb-5 g-3">
                                    <div class="col-lg-3 col-md-6 d-flex align-items-center">
                                        <span class="text-nowrap me-2 fw-bold text-muted">代購人</span>
                                        <input type="text" class="form-control custom-input" value="{{ $post->user->name ?? '系統匹配' }}" readonly>
                                    </div>
                                    <div class="col-lg-4 col-md-6 d-flex align-items-center">
                                        <span class="text-nowrap me-2 fw-bold text-muted">銷售期間：</span>
                                        <input type="text" class="form-control custom-input" 
                                               value="{{ \Carbon\Carbon::parse($post->start_date)->format('Y-m-d') }} ~ {{ \Carbon\Carbon::parse($post->end_date)->format('Y-m-d') }}" 
                                               readonly>
                                    </div>
                                    <div class="col-lg-5 col-md-12 d-flex align-items-center">
                                        <span class="text-nowrap me-2 fw-bold text-muted">描述訊息：</span>
                                        <input type="text" class="form-control custom-input" value="{{ $post->description }}" readonly>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    
                                    {{-- 左側：商品圖片輪播 (Slider) --}}
                                    <div class="col-md-4">
                                        @php
                                            $modalImages = $post->products->filter(fn($product) => $product->image_path);
                                        @endphp

                                        @if($modalImages->isNotEmpty())
                                            <div id="carouselModal{{ $post->id }}" class="carousel slide modal-slider-container" data-bs-ride="carousel">
                                                <div class="modal-slider-name">
                                                    <span class="modal-slider-name-text">{{ $modalImages->first()->name }}</span>
                                                </div>
                                                @if($modalImages->count() > 1)
                                                    <div class="carousel-indicators mb-2">
                                                        @foreach($modalImages as $index => $product)
                                                            <button type="button" 
                                                                    data-bs-target="#carouselModal{{ $post->id }}" 
                                                                    data-bs-slide-to="{{ $index }}" 
                                                                    class="{{ $loop->first ? 'active' : '' }}" 
                                                                    aria-current="{{ $loop->first ? 'true' : 'false' }}" 
                                                                    aria-label="Slide {{ $index + 1 }}"></button>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                <div class="carousel-inner h-100">
                                                    @foreach($modalImages as $index => $product)
                                                        <div class="carousel-item h-100 {{ $loop->first ? 'active' : '' }}" data-product-name="{{ $product->name }}">
                                                            <img src="{{ route('post-product.image', ['postProduct' => $product->id, 'v' => $product->updated_at?->timestamp ?? now()->timestamp]) }}" class="modal-slider-img" alt="商品圖片 {{ $index + 1 }}">
                                                        </div>
                                                    @endforeach
                                                </div>

                                                @if($modalImages->count() > 1)
                                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselModal{{ $post->id }}" data-bs-slide="prev">
                                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Previous</span>
                                                    </button>
                                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselModal{{ $post->id }}" data-bs-slide="next">
                                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Next</span>
                                                    </button>
                                                @endif
                                            </div>
                                        @else
                                            <div class="modal-slider-container img-placeholder">
                                                <i class="bi bi-image fs-1 me-2"></i> 無商品圖片
                                            </div>
                                        @endif
                                    </div>
                                    
                                    {{-- 右側：表格區塊 --}}
                                    <div class="col-md-8 d-flex flex-column">
                                        <div class="table-responsive flex-grow-1">
                                            <table class="table align-middle text-center custom-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>商品名稱</th>
                                                        <th>可下單數量</th>
                                                        <th>單價</th>
                                                        <th>數量</th>
                                                        <th>小計</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($post->products as $product)
                                                        <tr class="product-row" data-price="{{ $product->price }}" data-max="{{ $product->max_quantity }}">
                                                            <td>{{ $product->name }}</td>
                                                            <td>{{ $product->max_quantity }}</td>
                                                            <td>${{ number_format($product->price) }}</td>
                                                            
                                                            <td>
                                                                <div class="d-flex align-items-center justify-content-center">
                                                                    <button class="qty-btn btn-minus" type="button">−</button>
                                                                    <span class="mx-3 fw-bold qty-text">0</span>
                                                                    <input type="hidden" name="items[{{ $product->id }}][quantity]" value="0" class="qty-hidden-input">
                                                                    <button class="qty-btn btn-plus" type="button">+</button>
                                                                </div>
                                                            </td>
                                                            
                                                            <td class="subtotal-text">$0</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-muted text-center py-4">此貼文目前沒有商品</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="mt-4 pt-3">
                                            <div class="text-end mb-4">
                                                <h5 class="fw-bold m-0 text-dark total-amount">總計金額：NT$0</h5>
                                            </div>
                                            <div class="d-flex justify-content-end gap-3">
                                                <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">再逛逛</button>
                                                {{-- 預設加入 disabled 屬性，並加上 btn-checkout class 以便操作 --}}
                                                <button type="submit" class="btn text-white px-4 py-2 btn-checkout" style="background-color: #63a388;" disabled>確認結帳</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">目前沒有符合條件的代購貼文</p>
                </div>
            @endforelse
        </div>

        <div class="mt-5 d-flex justify-content-center">
            {{ $posts->links() }}
        </div>
    </div>

    {{-- 負責計算數量與金額的 JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.body.addEventListener('click', function(e) {
                if ((e.target.classList.contains('btn-plus') || e.target.classList.contains('btn-minus')) && e.target.closest('.product-row')) {
                    
                    const isPlus = e.target.classList.contains('btn-plus');
                    const row = e.target.closest('.product-row');
                    const modal = e.target.closest('.modal-content');
                    
                    const price = parseFloat(row.dataset.price);
                    const maxQty = parseInt(row.dataset.max);
                    
                    const qtyText = row.querySelector('.qty-text');
                    const qtyInput = row.querySelector('.qty-hidden-input');
                    const subtotalText = row.querySelector('.subtotal-text');
                    
                    let currentQty = parseInt(qtyText.innerText);

                    if (isPlus) {
                        if (currentQty < maxQty) currentQty++; 
                    } else {
                        if (currentQty > 0) currentQty--;      
                    }

                    qtyText.innerText = currentQty;
                    qtyInput.value = currentQty;

                    const subtotal = currentQty * price;
                    subtotalText.innerText = '$' + subtotal.toLocaleString();

                    updateTotal(modal);
                }
            });

            function updateTotal(modal) {
                let total = 0;
                let totalQty = 0; // 用來計算總數量
                const rows = modal.querySelectorAll('.product-row');
                
                rows.forEach(row => {
                    const price = parseFloat(row.dataset.price);
                    const qty = parseInt(row.querySelector('.qty-text').innerText);
                    total += (price * qty);
                    totalQty += qty; // 累加目前這項商品的數量
                });
                
                modal.querySelector('.total-amount').innerText = '總計金額：NT$' + total.toLocaleString();

                // 判斷總數量是否大於0，決定是否解除禁用「確認結帳」按鈕
                const checkoutBtn = modal.querySelector('.btn-checkout');
                if (checkoutBtn) {
                    checkoutBtn.disabled = (totalQty === 0);
                }
            }

            function initializeSliderProductLabels() {
                document.querySelectorAll('.modal-slider-container.carousel').forEach(carousel => {
                    const label = carousel.querySelector('.modal-slider-name-text');
                    const activeItem = carousel.querySelector('.carousel-item.active');
                    if (label && activeItem) {
                        label.textContent = activeItem.dataset.productName || '';
                    }

                    carousel.addEventListener('slide.bs.carousel', function(event) {
                        const nextItem = event.relatedTarget;
                        if (label && nextItem) {
                            label.textContent = nextItem.dataset.productName || '';
                        }
                    });
                });
            }

            initializeSliderProductLabels();
        });
    </script>
    
</x-app-layout>