@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h2 class="fw-bold mb-1">管理員後台</h2>
            <p class="text-muted mb-0">歡迎，{{ $adminName }}。</p>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success rounded-4">{{ session('status') }}</div>
    @endif

    <ul class="nav nav-pills mb-3" id="adminTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="agent-tab" data-bs-toggle="pill" data-bs-target="#agent-pane" type="button" role="tab">管理申請代購人</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="request-tab" data-bs-toggle="pill" data-bs-target="#request-pane" type="button" role="tab">管理請購清單</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="post-tab" data-bs-toggle="pill" data-bs-target="#post-pane" type="button" role="tab">管理代購貼文</button>
        </li>
    </ul>

    <div class="tab-content" id="adminTabContent">
        <div class="tab-pane fade show active" id="agent-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>申請人</th>
                                    <th>國家</th>
                                    <th>電話號碼</th>
                                    <th>身份證字號</th>
                                    <th>狀態</th>
                                    <th class="text-end">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($agentApplications as $application)
                                    @php
                                        $applicantName = $application->name ?? optional($application->user)->name ?? '未提供';
                                        $country = $application->country ?? $application->main_region ?? '未提供';
                                        $idNumber = $application->id_number ?? $application->ID_Card ?? '未提供';
                                        $status = $application->status ?? 'pending';
                                    @endphp
                                    <tr>
                                        <td>{{ $applicantName }}</td>
                                        <td>{{ $country }}</td>
                                        <td>{{ $application->phone ?? '未提供' }}</td>
                                        <td>{{ $idNumber }}</td>
                                        <td>
                                            @if ($status === 'approved')
                                                <span class="badge text-bg-success">通過</span>
                                            @elseif ($status === 'rejected')
                                                <span class="badge text-bg-danger">不通過</span>
                                            @elseif ($status === 'resubmitted')
                                                <span class="badge text-bg-warning text-dark">重新申請中...</span>
                                            @else
                                                <span class="badge text-bg-secondary">待審核</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#agentViewModal-{{ $application->id }}">檢視</button>

                                            @if (in_array($status, ['pending', 'resubmitted']))
                                                <form method="POST" action="{{ route('admin.agent-applications.approve', $application) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-success">審核通過</button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.agent-applications.reject', $application) }}" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">審核不通過</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">目前沒有代購人申請資料</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @foreach($agentApplications as $application)
                @php
                    $applicantName = $application->name ?? optional($application->user)->name ?? '未提供';
                    $country = $application->country ?? $application->main_region ?? '未提供';
                                        $idNumber = $application->id_number ?? $application->ID_Card ?? '未提供';
                    $statusLabel = [
                        'pending' => '待審核',
                        'resubmitted' => '重新申請中...',
                        'approved' => '通過',
                        'rejected' => '不通過',
                    ][$application->status] ?? $application->status;
                @endphp
                <div class="modal fade" id="agentViewModal-{{ $application->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4">
                            <div class="modal-header">
                                <h5 class="modal-title">代購人申請檢視</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-1"><strong>申請人：</strong>{{ $applicantName }}</p>
                                <p class="mb-1"><strong>國家：</strong>{{ $country }}</p>
                                <p class="mb-1"><strong>電話號碼：</strong>{{ $application->phone ?? '未提供' }}</p>
                                <p class="mb-1"><strong>身份證字號：</strong>{{ $idNumber }}</p>
                                <p class="mb-0"><strong>狀態：</strong>{{ $statusLabel }}</p>

                                <div class="mt-3">
                                    <p class="mb-2"><strong>身份證正面：</strong></p>
                                    @if($application->id_image_front)
                                        <a href="{{ route('admin.agent-applications.identity-image', ['agentApplication' => $application->id, 'side' => 'front']) }}" target="_blank" rel="noopener noreferrer">
                                            <img src="{{ route('admin.agent-applications.identity-image', ['agentApplication' => $application->id, 'side' => 'front']) }}"
                                                alt="身份證正面"
                                                class="img-fluid rounded border mb-3"
                                                style="max-height: 150px; object-fit: contain;">
                                        </a>
                                    @else
                                        <p class="text-muted small">未提供身份證正面照片</p>
                                    @endif

                                    <p class="mb-2"><strong>身份證背面：</strong></p>
                                    @if($application->id_image_back)
                                        <a href="{{ route('admin.agent-applications.identity-image', ['agentApplication' => $application->id, 'side' => 'back']) }}" target="_blank" rel="noopener noreferrer">
                                            <img src="{{ route('admin.agent-applications.identity-image', ['agentApplication' => $application->id, 'side' => 'back']) }}"
                                                alt="身份證背面"
                                                class="img-fluid rounded border"
                                                style="max-height: 150px; object-fit: contain;">
                                        </a>
                                    @else
                                        <p class="text-muted small">未提供身份證背面照片</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="tab-pane fade" id="request-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>請購人</th>
                                    <th>商品</th>
                                    <th>國家</th>
                                    <th>截止日</th>
                                    <th>狀態</th>
                                    <th class="text-end">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requestLists as $request)
                                    @php
                                        $items = $request->items ?? collect();
                                        $firstItem = optional($items->first())->name ?? $request->title;
                                        $extraItems = $items->slice(1);
                                        $countryLabel = [
                                            'jp' => '日本',
                                            'kr' => '韓國',
                                            'us' => '美國',
                                            'gb' => '英國',
                                        ][$request->country] ?? $request->country;
                                        $statusLabel = [
                                            'editing' => '編輯中',
                                            'pending' => '等待接單',
                                            'offered' => '代購人已關注',
                                            'matched' => '已確認代購人',
                                            'completed' => '訂單已完成',
                                            'cancelled' => '訂單已取消',
                                        ][$request->status] ?? $request->status;
                                    @endphp
                                    <tr>
                                        <td>{{ optional($request->user)->name ?? '未提供' }}</td>
                                        <td>
                                            @if($extraItems->isNotEmpty())
                                                <details>
                                                    <summary class="cursor-pointer">{{ $firstItem }}（另有 {{ $extraItems->count() }} 項）</summary>
                                                    <ul class="mb-0 mt-2 ps-3 text-muted small">
                                                        @foreach($extraItems as $item)
                                                            <li>{{ $item->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                </details>
                                            @else
                                                {{ $firstItem }}
                                            @endif
                                        </td>
                                        <td>{{ $countryLabel }}</td>
                                        <td>{{ optional($request->deadline)->format('Y-m-d') }}</td>
                                        <td>{{ $statusLabel }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#requestViewModal-{{ $request->id }}">檢視</button>
                                            <form method="POST" action="{{ route('admin.request-lists.delete', $request) }}" class="d-inline" onsubmit="return confirm('定要刪除此請購清單嗎？');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">刪除</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">目前沒有請購清單資料</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @foreach($requestLists as $request)
                <div class="modal fade" id="requestViewModal-{{ $request->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4">
                            <div class="modal-header">
                                <h5 class="modal-title">請購清單檢視</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                @php
                                    $firstItemWithImage = $request->items->first(fn ($item) => !empty($item->reference_image));
                                @endphp
                                <div class="row g-3 align-items-start">
                                    <div class="col-md-7">
                                        <p class="mb-1"><strong>國家：</strong>{{ [
                                            'jp' => '日本',
                                            'kr' => '韓國',
                                            'us' => '美國',
                                            'gb' => '英國',
                                        ][$request->country] ?? $request->country }}</p>
                                        <p class="mb-1"><strong>截止日：</strong>{{ optional($request->deadline)->format('Y-m-d') }}</p>
                                        <p class="mb-2"><strong>狀態：</strong>{{ [
                                            'editing' => '編輯中',
                                            'pending' => '等待接單',
                                            'offered' => '代購人已關注',
                                            'matched' => '已確認代購人',
                                            'completed' => '訂單已完成',
                                            'cancelled' => '訂單已取消',
                                        ][$request->status] ?? $request->status }}</p>

                                        <div class="fw-semibold mb-1">商品清單</div>
                                        <ul class="mb-0 ps-0 list-unstyled">
                                            @foreach($request->items as $item)
                                                <li class="mb-2">
                                                    <button
                                                        type="button"
                                                        class="btn btn-link p-0 text-start text-decoration-none request-item-preview-trigger"
                                                        data-preview-image="{{ $item->reference_image ? route('admin.request-items.image', $item) : '' }}"
                                                        data-preview-name="{{ $item->name }}"
                                                    >
                                                        • {{ $item->name }}　需求量: {{ (int) ($item->quantity ?? 0) }}
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="border rounded-3 bg-light d-flex align-items-center justify-content-center overflow-hidden"
                                             style="min-height: 180px;">
                                            <img
                                                src="{{ $firstItemWithImage ? route('admin.request-items.image', $firstItemWithImage) : '' }}"
                                                alt="{{ $firstItemWithImage?->name ?? '商品圖片預覽' }}"
                                                class="request-item-preview-image img-fluid w-100 h-100"
                                                style="object-fit: contain; {{ $firstItemWithImage ? '' : 'display:none;' }}"
                                            >
                                            <span class="request-item-preview-empty text-muted small {{ $firstItemWithImage ? 'd-none' : '' }}">
                                                尚未提供商品圖片
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="tab-pane fade" id="post-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                             <thead>
                                <tr>
                                     <th>代購人</th>
                                    <th>商品</th>
                                    <th>國家</th>
                                    <th>代購期間</th>
                                    <th>狀態</th>
                                    <th class="text-end">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posts as $post)
                                    @php
                                        $products = $post->products ?? collect();
                                        $firstProductName = optional($products->first())->name ?? '未提供商品';
                                        $extraProductCount = max($products->count() - 1, 0);
                                    @endphp
                                    <tr>
                                        <td>{{ optional($post->user)->name ?? '未提供' }}</td>
                                        <td>
                                            @if($extraProductCount > 0)
                                                <details>
                                                    <summary class="cursor-pointer">{{ $firstProductName }}（另有 {{ $extraProductCount }} 項）</summary>
                                                    <ul class="mb-0 mt-2 ps-3 text-muted small">
                                                        @foreach($products->slice(1) as $product)
                                                            <li>{{ $product->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                </details>
                                            @else
                                                {{ $firstProductName }}
                                            @endif
                                        </td>
                                        <td>
                                            {{ $post->country ?? '未提供' }}{{ $post->city ? '・' . $post->city : '' }}
                                        </td>
                                        <td>
                                            {{ optional($post->start_date)->format('Y-m-d') ?? '未提供' }}
                                            ~
                                            {{ optional($post->end_date)->format('Y-m-d') ?? '未提供' }}
                                        </td>
                                        <td>{{ $post->status }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#postViewModal-{{ $post->id }}">檢視</button>
                                            <form method="POST" action="{{ route('admin.agent-posts.delete', $post) }}" class="d-inline" onsubmit="return confirm('確定要刪除此代購貼文嗎？');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">刪除</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">目前沒有代購貼文資料</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @foreach($posts as $post)
                <div class="modal fade" id="postViewModal-{{ $post->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4">
                            <div class="modal-header">
                                <h5 class="modal-title">代購貼文檢視</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                @php
                                    $firstPostProductWithImage = $post->products->first(fn ($product) => !empty($product->image_path));
                                @endphp
                                <div class="row g-3 align-items-start">
                                    <div class="col-md-8">
                                        <p class="mb-1"><strong>國家：</strong>{{ $post->country ?? '未提供' }}{{ $post->city ? '・' . $post->city : '' }}</p>
                                        <p class="mb-1"><strong>貼文標題：</strong>{{ $post->title ?? '未提供' }}</p>
                                        <p class="mb-1"><strong>貼文描述：</strong>{{ $post->description ?? '未提供' }}</p>
                                        <p class="mb-1"><strong>代購期間：</strong>
                                            {{ optional($post->start_date)->format('Y-m-d') ?? '未提供' }}
                                            ~
                                            {{ optional($post->end_date)->format('Y-m-d') ?? '未提供' }}
                                        </p>
                                        <p class="mb-3"><strong>狀態：</strong>{{ $post->status ?? '未提供' }}</p>

                                        <div class="fw-semibold mb-2">商品清單</div>
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>商品名稱</th>
                                                        <th>單價</th>
                                                        <th>最高數量</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($post->products as $product)
                                                        <tr class="post-product-preview-trigger" style="cursor: pointer;"
                                                            data-preview-image="{{ $product->image_path ? route('post-product.image', $product) : '' }}"
                                                            data-preview-name="{{ $product->name ?? '商品圖片' }}">
                                                            <td>{{ $product->name ?? '未提供' }}</td>
                                                            <td>NT$ {{ number_format((float) ($product->price ?? 0), 0) }}</td>
                                                            <td>{{ (int) ($product->max_quantity ?? 0) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center text-muted">目前沒有商品資料</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="border rounded-3 bg-light d-flex align-items-center justify-content-center overflow-hidden"
                                             style="min-height: 200px;">
                                            <img
                                                src="{{ $firstPostProductWithImage ? route('post-product.image', $firstPostProductWithImage) : '' }}"
                                                alt="{{ $firstPostProductWithImage?->name ?? '商品圖片預覽' }}"
                                                class="post-product-preview-image img-fluid w-100 h-100"
                                                style="object-fit: contain; {{ $firstPostProductWithImage ? '' : 'display:none;' }}"
                                            >
                                            <span class="post-product-preview-empty text-muted small {{ $firstPostProductWithImage ? 'd-none' : '' }}">
                                                尚未提供商品圖片
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
<script>
document.addEventListener('click', function (event) {
    const trigger = event.target.closest('.request-item-preview-trigger');
    if (!trigger) {
        return;
    }

    const modalBody = trigger.closest('.modal-body');
    if (!modalBody) {
        return;
    }

    const image = modalBody.querySelector('.request-item-preview-image');
    const empty = modalBody.querySelector('.request-item-preview-empty');
    const imageUrl = trigger.dataset.previewImage || '';
    const imageName = trigger.dataset.previewName || '商品圖片預覽';

    if (imageUrl) {
        image.src = imageUrl;
        image.alt = imageName;
        image.style.display = '';
        empty?.classList.add('d-none');
        return;
    }

    image.removeAttribute('src');
    image.alt = imageName;
    image.style.display = 'none';
    empty?.classList.remove('d-none');
});

document.addEventListener('click', function (event) {
    const trigger = event.target.closest('.post-product-preview-trigger');
    if (!trigger) {
        return;
    }

    const modalBody = trigger.closest('.modal-body');
    if (!modalBody) {
        return;
    }

    const image = modalBody.querySelector('.post-product-preview-image');
    const empty = modalBody.querySelector('.post-product-preview-empty');
    const imageUrl = trigger.dataset.previewImage || '';
    const imageName = trigger.dataset.previewName || '商品圖片預覽';

    if (imageUrl) {
        image.src = imageUrl;
        image.alt = imageName;
        image.style.display = '';
        empty?.classList.add('d-none');
        return;
    }

    image.removeAttribute('src');
    image.alt = imageName;
    image.style.display = 'none';
    empty?.classList.remove('d-none');
});
</script>
@endsection