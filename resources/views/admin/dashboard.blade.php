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
                                        <a href="{{ asset('storage/' . $application->id_image_front) }}" target="_blank" rel="noopener noreferrer">
                                            <img src="{{ asset('storage/' . $application->id_image_front) }}"
                                                alt="身份證正面"
                                                class="img-fluid rounded border mb-3"
                                                style="max-height: 260px; object-fit: contain;">
                                        </a>
                                    @else
                                        <p class="text-muted small">未提供身份證正面照片</p>
                                    @endif

                                    <p class="mb-2"><strong>身份證背面：</strong></p>
                                    @if($application->id_image_back)
                                        <a href="{{ asset('storage/' . $application->id_image_back) }}" target="_blank" rel="noopener noreferrer">
                                            <img src="{{ asset('storage/' . $application->id_image_back) }}"
                                                alt="身份證背面"
                                                class="img-fluid rounded border"
                                                style="max-height: 260px; object-fit: contain;">
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
                                    <tr><td colspan="5" class="text-center text-muted">目前沒有請購清單資料</td></tr>
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
                                <ul class="mb-0">
                                    @foreach($request->items as $item)
                                        <li>{{ $item->name }}</li>
                                    @endforeach
                                </ul>
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
                                    <th>貼文標題</th>
                                    <th>狀態</th>
                                    <th>建立時間</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posts as $post)
                                    <tr>
                                        <td>{{ $post->title }}</td>
                                        <td>{{ $post->status }}</td>
                                        <td>{{ optional($post->created_at)->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">目前沒有代購貼文資料</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection