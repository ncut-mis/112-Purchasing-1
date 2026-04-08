@extends('layouts.front')

@section('content')
<section class="py-5" style="background: linear-gradient(135deg, #e9f6f4 0%, #f3f7f5 100%); min-height: calc(100vh - 80px);">
    <div class="container" style="max-width: 900px;">
        <div class="mb-4 d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div>
                <h1 class="fw-bold mb-1" style="color:#2c3e50;">請購清單聊天室</h1>
                <p class="text-muted mb-0">此對話僅限該請購單的請購人與接單代購人可見。</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width:44px;height:44px;" aria-label="關閉聊天室">
                ✕
            </a>
        </div>

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 px-4">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <div class="text-muted small">請購單 #{{ $requestList->id }}</div>
                        <h5 class="fw-bold mb-0" style="color:#2c3e50;">{{ $requestList->title }}</h5>
                    </div>
                    <div class="small text-muted">截止日：{{ optional($requestList->deadline)->format('Y-m-d') ?? '-' }}</div>
                </div>
            </div>

            <div class="card-body p-0 d-flex flex-column" style="height: 70vh; min-height: 520px;">
                <div id="chat-messages" class="flex-grow-1 p-4" style="background:#f8fbfa; overflow-y:auto;">
                    @forelse($messages as $message)
                        @php($isMine = (int) $message->sender_id === (int) auth()->id())
                        <div class="d-flex mb-3 {{ $isMine ? 'justify-content-end' : 'justify-content-start' }}">
                            <div style="max-width: 75%;">
                                <div class="px-3 py-2 rounded-3 border {{ $isMine ? 'bg-success-subtle' : 'bg-white' }}" style="border-color:#d7e3df;color:#2c3e50;">
                                    <div class="small text-muted mb-1">{{ $message->sender->name ?? '使用者' }}</div>
                                    <div>{{ $message->body }}</div>
                                </div>
                                <div class="small text-muted mt-1 {{ $isMine ? 'text-end' : 'text-start' }}">
                                    {{ optional($message->created_at)->format('Y-m-d H:i') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">目前尚無訊息，開始第一句對話吧。</div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('request-list.chat.store', $requestList) }}" class="border-top p-3 bg-light-subtle d-flex gap-2 align-items-center">
                    @csrf
                    <input
                        name="body"
                        type="text"
                        class="form-control rounded-pill px-4"
                        placeholder="輸入要傳送給對方的訊息"
                        autocomplete="off"
                        required
                        maxlength="2000"
                    >
                    <button type="submit" class="btn btn-success rounded-pill px-4 text-nowrap">傳送</button>
                </form>

                @if ($errors->any())
                    <div class="px-3 pb-3">
                        <div class="alert alert-danger mb-0 py-2">{{ $errors->first() }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const box = document.getElementById('chat-messages');
        if (box) box.scrollTop = box.scrollHeight;
    });
</script>
@endsection