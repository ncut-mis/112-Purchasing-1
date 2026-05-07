<x-app-layout>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-xl">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">報價詳細內容</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="text-muted small uppercase">代購人</label>
                            <div class="fw-semibold text-lg">{{ $quote->user->name }}</div>
                        </div>

                        <div class="mb-4">
                            <label class="text-muted small">總報價金額 (含服務費)</label>
                            <div class="text-success h3 fw-bold">${{ number_format($quote->price) }}</div>
                        </div>

                        <div class="mb-4">
                            <label class="text-muted small">預計交期 / 備註</label>
                            <div class="p-3 bg-light rounded">{{ $quote->comment }}</div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <form action="{{ route('quotes.accept', $quote->id) }}" method="POST" onsubmit="return confirm('確定要接受此報價並委託該代購人嗎？')">
                                @csrf
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="bi bi-check-circle me-1"></i> 接受並選定
                                </button>
                            </form>

                            <form action="{{ route('quotes.reject', $quote->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger">
                                    拒絕報價
                                </button>
                            </form>

                            <a href="{{ route('dashboard') }}" class="btn btn-link text-secondary">返回列表</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>