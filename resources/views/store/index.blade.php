<x-app-layout>
    <!-- 引入 Bootstrap 資源 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        .store-container { 
        max-width: 1400px; 
        margin: 0 auto; 
        padding: 40px 20px; 
    }
    
    /* 2. 搜尋欄美化：增加 padding 與更柔和的陰影 */
    .search-wrapper {
        background: #fff;
        border-radius: 50px;
        padding: 10px 12px; /* 增加上下內距，讓搜尋列變厚 */
        box-shadow: 0 10px 30px rgba(0,0,0,0.05); /* 陰影更深更遠，提升質感 */
        border: 1px solid #f0f0f0;
        width: 100%; /* 確保填滿父容器 */
    }

    /* 3. 輸入框：字體稍微加大 */
    .search-input { 
        border: none !important; 
        box-shadow: none !important; 
        padding-left: 15px;
        font-size: 1.05rem; 
    }

    /* 4. 下拉選單：增加左右間距 */
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

    /* 5. 搜尋按鈕：增加寬度使其更顯眼 */
    .btn-search-submit {
        background-color: #5A9E8E;
        border-radius: 50px;
        padding: 10px 25px; /* 增加左右內距 */
        color: white;
        border: none;
        transition: 0.3s;
        
        /* 關鍵修正：強制文字橫向排列不換行 */
        white-space: nowrap; 
        
        /* 確保按鈕有足夠的最小寬度 */
        min-width: fit-content; 
        
        /* 避免寬度被外部容器限制 */
        flex-shrink: 0; 
    }
    .btn-search-submit:hover { background-color: #4a8376; transform: scale(1.02); }
        /* 卡片樣式保持一致 */
        .post-card { border: none; border-radius: 20px; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; height: 100%; transition: transform 0.3s ease; }
        .post-card:hover { transform: translateY(-5px); }
        .card-img-placeholder { height: 180px; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; position: relative; }
        .badge-country { position: absolute; top: 15px; left: 15px; background: rgba(0,0,0,0.05); color: #666; font-size: 0.7rem; padding: 4px 10px; border-radius: 50px; }
        .badge-status { position: absolute; top: 15px; right: 15px; background: #198754; color: #fff; font-size: 0.7rem; padding: 4px 10px; border-radius: 50px; }
        .btn-expand { border: 1px solid #eee; border-radius: 50px; color: #888; font-size: 0.85rem; width: 100%; padding: 8px; background: white; }
    </style>

    <div class="store-container">
        
        {{-- 1. 標題與搜尋區塊 --}}
       
            
            <div class="store-container">
    {{-- 利用 Flex 佈局讓標題在左，寬大的搜尋列在右 --}}
    <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between mb-5 gap-4">
        
        {{-- 左側標題區 --}}
        <div class="text-nowrap">
            <h6 class="text-success fw-bold text-uppercase mb-1" style="letter-spacing: 2px; font-size: 0.8rem;">Agent Posts</h6>
            <h2 class="fw-bold mb-0">最新代購貼文</h2>
        </div>
        
        {{-- 右側搜尋區：使用 flex-grow 撐開空間 --}}
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

        {{-- 2. 搜尋狀態提示 (修正點：直接使用 request 獲取值) --}}
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

        {{-- 3. 貼文網格：將 $agentPosts 改回 $posts --}}
        <div class="row g-4">
            @forelse($posts as $post) {{-- 這裡必須用 $posts --}}
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card post-card">
                        <div class="card-img-placeholder">
                            {{-- 這裡對應循環內的單數變數 $post --}}
                            <span class="badge-country">{{ $post->country }}</span>
                            <span class="badge-status">接單中</span>
                            <i class="bi bi-image"></i>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold">{{ $post->title }}</h5>
                            <p class="text-muted small mb-4">
                                {{ \Illuminate\Support\Str::limit($post->description ?: '無詳細說明。', 60) }}
                            </p>
                            <button class="btn btn-expand">
                                展開詳細資訊 <i class="bi bi-chevron-down ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">目前沒有符合條件的代購貼文</p>
                </div>
            @endforelse
        </div>

        {{-- 4. 分頁：將 $agentPosts 改回 $posts --}}
        <div class="mt-5 d-flex justify-content-center">
            {{ $posts->links() }}
        </div>
    </div>
</x-app-layout>