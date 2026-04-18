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
            <button class="nav-link" id="violation-tab" data-bs-toggle="pill" data-bs-target="#violation-pane" type="button" role="tab">管理違規內容</button>
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

        <div class="tab-pane fade" id="violation-pane" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-lg-5" style="min-height: 620px;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <p class="text-muted mb-0">目前審核違規內容方式：<span class="fw-semibold text-dark">{{ $reviewModeLabel ?? '人工審核' }}</span></p>
                        </div>
                        <button
                            type="button"
                            class="btn btn-outline-secondary rounded-2"
                            data-bs-toggle="modal"
                            data-bs-target="#reviewModeModal"
                            aria-label="審核模式設定"
                            title="審核模式設定"
                        >
                            <i class="bi bi-gear me-2"></i>設定
                        </button>
                    </div>

                    <ul class="nav nav-pills gap-2 mb-4" id="violationSwitchTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link active px-4 fw-semibold"
                                id="violation-request-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#violation-request-pane"
                                type="button"
                                role="tab"
                                aria-controls="violation-request-pane"
                                aria-selected="true"
                            >
                                請託單
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link px-4 fw-semibold"
                                id="violation-post-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#violation-post-pane"
                                type="button"
                                role="tab"
                                aria-controls="violation-post-pane"
                                aria-selected="false"
                            >
                                代購貼文
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="violationSwitchContent">
                        <div class="tab-pane fade show active" id="violation-request-pane" role="tabpanel" aria-labelledby="violation-request-tab" tabindex="0">
                            <p class="text-muted mb-3">以下為被檢舉違規的請託單</p>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 200px;">請託單</th>
                                            <th>檢舉人</th>
                                            <th>檢舉違規類型</th>
                                            <th style="min-width: 260px;">檢舉原因</th>
                                            <th>檢舉日期</th>
                                            <th>狀態</th>
                                            <th class="text-end">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($requestListReports as $report)
                                            <tr>
                                                <td>{{ optional($report->reportable)->title ?? '內容已不存在' }}</td>
                                                <td>{{ optional($report->reporter)->name ?? '匿名檢舉者' }}</td>
                                                <td>{{ \App\Models\ContentReport::typeLabel((string) $report->report_type) }}</td>
                                                <td>{{ $report->reason }}</td>
                                                <td>{{ optional($report->created_at)->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    @if($report->status === \App\Models\ContentReport::STATUS_PENDING)
                                                        <span class="badge text-bg-warning text-dark">待審核</span>
                                                    @elseif($report->status === \App\Models\ContentReport::STATUS_APPROVED)
                                                        <span class="badge text-bg-success">已判定成立</span>
                                                    @else
                                                        <span class="badge text-bg-secondary">已判定不成立</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reportViewModal-{{ $report->id }}">檢視</button>
                                                    @if($report->status === \App\Models\ContentReport::STATUS_PENDING)
                                                        <form method="POST" action="{{ route('admin.reports.approve', $report) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-success">檢舉成立</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.reports.reject', $report) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-danger">檢舉不成立</button>
                                                        </form>
                                                    @elseif($reviewMode === 'auto' && in_array($report->status, [\App\Models\ContentReport::STATUS_APPROVED, \App\Models\ContentReport::STATUS_REJECTED]))
                                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#overrideDecisionModal-{{ $report->id }}">更改判定</button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">目前沒有請託單檢舉資料</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="violation-post-pane" role="tabpanel" aria-labelledby="violation-post-tab" tabindex="0">
                            <p class="text-muted mb-3">以下為被檢舉違規的代購貼文</p>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 200px;">代購貼文</th>
                                            <th>檢舉人</th>
                                            <th>檢舉違規類型</th>
                                            <th style="min-width: 260px;">檢舉原因</th>
                                            <th>檢舉日期</th>
                                            <th>狀態</th>
                                            <th class="text-end">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($agentPostReports as $report)
                                            <tr>
                                                <td>{{ optional($report->reportable)->title ?? '內容已不存在' }}</td>
                                                <td>{{ optional($report->reporter)->name ?? '匿名檢舉者' }}</td>
                                                <td>{{ \App\Models\ContentReport::typeLabel((string) $report->report_type) }}</td>
                                                <td>{{ $report->reason }}</td>
                                                <td>{{ optional($report->created_at)->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    @if($report->status === \App\Models\ContentReport::STATUS_PENDING)
                                                        <span class="badge text-bg-warning text-dark">待審核</span>
                                                    @elseif($report->status === \App\Models\ContentReport::STATUS_APPROVED)
                                                        <span class="badge text-bg-success">已判定成立</span>
                                                    @else
                                                        <span class="badge text-bg-secondary">已判定不成立</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reportViewModal-{{ $report->id }}">檢視</button>
                                                    @if($report->status === \App\Models\ContentReport::STATUS_PENDING)
                                                        <form method="POST" action="{{ route('admin.reports.approve', $report) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-success">檢舉成立</button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.reports.reject', $report) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm btn-danger">檢舉不成立</button>
                                                        </form>
                                                    @elseif($reviewMode === 'auto' && in_array($report->status, [\App\Models\ContentReport::STATUS_APPROVED, \App\Models\ContentReport::STATUS_REJECTED]))
                                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#overrideDecisionModal-{{ $report->id }}">更改判定</button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">目前沒有代購貼文檢舉資料</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="reviewModeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">違規內容審核方式設定</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-3">
                    目前審核違規內容方式：
                    <span class="fw-bold">{{ $reviewModeLabel ?? '人工審核' }}</span>
                </p>
                <p class="small text-muted mb-4">
                    切換為自動審核後，系統會在使用者送出檢舉時，依據請託單與代購貼文文字內容、圖片路徑文字、檢舉理由與檢舉類型進行一致性判斷，自動標記「檢舉成立」或「檢舉不成立」。
                </p>

                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('admin.review-mode.update') }}" class="flex-fill">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="mode" value="manual">
                        <button type="submit" class="btn {{ ($reviewMode ?? 'manual') === 'manual' ? 'btn-primary' : 'btn-outline-primary' }} w-100">
                            人工審核
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.review-mode.update') }}" class="flex-fill">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="mode" value="auto">
                        <button type="submit" class="btn {{ ($reviewMode ?? 'manual') === 'auto' ? 'btn-success' : 'btn-outline-success' }} w-100">
                            自動審核
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@foreach($requestListReports->merge($agentPostReports) as $report)
    @if($reviewMode === 'auto' && in_array($report->status, [\App\Models\ContentReport::STATUS_APPROVED, \App\Models\ContentReport::STATUS_REJECTED]))
        <div class="modal fade" id="overrideDecisionModal-{{ $report->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">更改檢舉判定</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <p class="mb-3">
                            目前判定：
                            <span class="fw-bold {{ $report->status === \App\Models\ContentReport::STATUS_APPROVED ? 'text-success' : 'text-secondary' }}">
                                {{ $report->status === \App\Models\ContentReport::STATUS_APPROVED ? '檢舉成立' : '檢舉不成立' }}
                            </span>
                        </p>
                        <p class="mb-4">您要將判定結果改為？</p>

                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('admin.reports.override', $report) }}" class="flex-fill">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="decision" value="approved">
                                <button type="submit" class="btn btn-success w-100">
                                    檢舉成立
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.reports.override', $report) }}" class="flex-fill">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="decision" value="rejected">
                                <button type="submit" class="btn btn-secondary w-100">
                                    檢舉不成立
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
@foreach($requestListReports->merge($agentPostReports) as $report)
    @php
        $reportable = $report->reportable;
        $isRequestListReport = $report->reportable_type === \App\Models\RequestList::class;
        $isAgentPostReport = $report->reportable_type === \App\Models\AgentPost::class;
        $detailCollapseId = $isRequestListReport
            ? 'request-list-report-detail-' . $report->id
            : 'agent-post-report-detail-' . $report->id;
    @endphp
    <div class="modal fade" id="reportViewModal-{{ $report->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title">檢舉內容詳情</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong>被檢舉內容：</strong>{{ optional($reportable)->title ?? '內容已不存在' }}</p>
                    <p class="mb-2"><strong>發布者：</strong>{{ optional(optional($reportable)->user)->name ?? '未知發布者' }}</p>
                    <p class="mb-2"><strong>檢舉人：</strong>{{ optional($report->reporter)->name ?? '匿名檢舉者' }}</p>
                    <p class="mb-2"><strong>檢舉違規類型：</strong>{{ \App\Models\ContentReport::typeLabel((string) $report->report_type) }}</p>
                    <p class="mb-2"><strong>檢舉原因：</strong>{{ $report->reason }}</p>
                    <p class="mb-2"><strong>檢舉日期：</strong>{{ optional($report->created_at)->format('Y-m-d H:i') }}</p>
                    <p class="mb-2"><strong>狀態：</strong>
                        @if($report->status === \App\Models\ContentReport::STATUS_PENDING)
                            待審核
                        @elseif($report->status === \App\Models\ContentReport::STATUS_APPROVED)
                            檢舉成立
                        @else
                            檢舉不成立
                        @endif
                    </p>

                    @if($isRequestListReport && $reportable)
                        <button
                            class="btn btn-outline-primary btn-sm mb-3"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $detailCollapseId }}"
                            aria-expanded="false"
                            aria-controls="{{ $detailCollapseId }}"
                        >
                            展開詳細檢舉請託單內容
                        </button>

                        <div class="collapse" id="{{ $detailCollapseId }}">
                            <div class="border rounded-3 p-3 bg-light-subtle">
                                <p class="mb-2"><strong>國家：</strong>{{ $reportable->country ?? '未提供' }}</p>
                                <p class="mb-2"><strong>店家名稱：</strong>{{ $reportable->store_name ?? $reportable->title ?? '未提供' }}</p>
                                <p class="mb-2"><strong>店家詳細地址：</strong>{{ $reportable->detail_address ?? $reportable->address_detail ?? '未提供' }}</p>
                                <p class="mb-2"><strong>商品截止日：</strong>{{ optional($reportable->deadline)->format('Y-m-d') ?? '未提供' }}</p>
                                <p class="mb-3"><strong>備註：</strong>{{ $reportable->note ?? '未提供' }}</p>

                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>商品名稱</th>
                                                <th>需求量</th>
                                                <th>商品圖片</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($reportable->items ?? [] as $item)
                                                <tr>
                                                    <td>{{ $item->name ?? '未命名商品' }}</td>
                                                    <td>{{ $item->quantity ?? 0 }}</td>
                                                    <td>
                                                        @if($item->reference_image)
                                                            <a href="{{ route('admin.request-items.image', $item) }}" target="_blank" rel="noopener noreferrer">
                                                                <img
                                                                    src="{{ route('admin.request-items.image', $item) }}"
                                                                    alt="商品圖片"
                                                                    class="rounded border"
                                                                    style="width: 56px; height: 56px; object-fit: cover;"
                                                                >
                                                            </a>
                                                        @else
                                                            <span class="text-muted">無圖片</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-muted text-center">此請託單尚無商品資料</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($isAgentPostReport && $reportable)
                        <button
                            class="btn btn-outline-primary btn-sm mb-3"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $detailCollapseId }}"
                            aria-expanded="false"
                            aria-controls="{{ $detailCollapseId }}"
                        >
                            展開詳細檢舉代購貼文內容
                        </button>

                        <div class="collapse" id="{{ $detailCollapseId }}">
                            <div class="border rounded-3 p-3 bg-light-subtle">
                                <p class="mb-2"><strong>國家：</strong>{{ $reportable->country ?? '未提供' }}</p>
                                <p class="mb-2"><strong>貼文標題：</strong>{{ $reportable->title ?? '未提供' }}</p>
                                <p class="mb-2"><strong>銷售期間：</strong>
                                    {{ optional($reportable->start_date)->format('Y-m-d') ?? '未提供' }}
                                    -
                                    {{ optional($reportable->end_date)->format('Y-m-d') ?? '未提供' }}
                                </p>
                                <p class="mb-3"><strong>描述訊息：</strong>{{ $reportable->description ?? '未提供' }}</p>

                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>商品名稱</th>
                                                <th>單價</th>
                                                <th>最高數量</th>
                                                <th>商品圖片</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($reportable->products ?? [] as $product)
                                                <tr>
                                                    <td>{{ $product->name ?? '未命名商品' }}</td>
                                                    <td>{{ number_format((float) ($product->price ?? 0), 0) }}</td>
                                                    <td>{{ $product->max_quantity ?? 0 }}</td>
                                                    <td>
                                                        @if($product->display_image_url)
                                                            <a href="{{ $product->display_image_url }}" target="_blank" rel="noopener noreferrer">
                                                                <img
                                                                    src="{{ $product->display_image_url }}"
                                                                    alt="商品圖片"
                                                                    class="rounded border"
                                                                    style="width: 56px; height: 56px; object-fit: cover;"
                                                                >
                                                            </a>
                                                        @elseif($product->image_path)
                                                            <a href="{{ asset('storage/' . ltrim($product->image_path, '/')) }}" target="_blank" rel="noopener noreferrer">
                                                                <img
                                                                    src="{{ asset('storage/' . ltrim($product->image_path, '/')) }}"
                                                                    alt="商品圖片"
                                                                    class="rounded border"
                                                                    style="width: 56px; height: 56px; object-fit: cover;"
                                                                >
                                                            </a>
                                                        @else
                                                            <span class="text-muted">無圖片</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-muted text-center">此代購貼文尚無商品資料</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach
<script>
document.addEventListener('DOMContentLoaded', function () {
    const openViolationTab = @json(request('tab') === 'violation');
    if (!openViolationTab) {
        return;
    }

    const violationTab = document.getElementById('violation-tab');
    if (!violationTab) {
        return;
    }

    const tab = new bootstrap.Tab(violationTab);
    tab.show();
});
</script>
@endsection