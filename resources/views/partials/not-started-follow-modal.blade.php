@php
    $notStartedTitle = $agentPost->title ?? '此代購團';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-0">
                <div class="text-center text-white px-4 py-5" style="background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white text-danger shadow mb-3" style="width: 72px; height: 72px;">
                        <i class="bi bi-hourglass-split fs-1"></i>
                    </div>
                    <h5 class="fw-black mb-2">還未到可跟團的時段</h5>
                    <p class="mb-0 opacity-90">請耐心等待，開放時間到後即可跟團。</p>
                </div>
                <div class="p-4">
                    <div class="rounded-4 border bg-light p-3 mb-4">
                        <div class="small text-muted fw-bold text-uppercase mb-1">代購團</div>
                        <div class="fw-bold text-dark mb-3">{{ $notStartedTitle }}</div>
                        <div class="small text-muted fw-bold text-uppercase mb-1">可跟團時段</div>
                        <div class="fw-semibold text-danger">
                            {{ optional($agentPost->start_date)->format('Y/m/d') }}
                            <span class="mx-1 text-muted">至</span>
                            {{ optional($agentPost->end_date)->format('Y/m/d') }}
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger rounded-pill w-100 py-2 fw-bold" data-bs-dismiss="modal">
                        我知道了
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
