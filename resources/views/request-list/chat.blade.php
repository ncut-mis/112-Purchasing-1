@extends('layouts.front')

@section('content')
<section class="py-5" style="background: linear-gradient(135deg, #e9f6f4 0%, #f3f7f5 100%); min-height: calc(100vh - 80px);">
    <div class="container" style="max-width: 900px;">
        <div class="mb-4 d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div>
                <h1 class="fw-bold mb-1" style="color:#2c3e50;">請託單聊天室</h1>
                <p class="text-muted mb-0">此對話僅限該請託單的請託人與接單代購人可見。</p>
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
        const form = document.querySelector('form[action*="chat"]');
        const input = document.querySelector('input[name="body"]');
        
        // 取得當前的使用者參數（供 JS 判斷攔截用）
        const currentUserId = @json(auth()->id());
        const requestListId = @json($requestList->id);
 
        // 進入網頁時將對話框自動滾動置底
        if (box) box.scrollTop = box.scrollHeight;
 
        // 💡【免重整修正：攔截傳統表單改用 AJAX 非同步傳送訊息】
        if (form && input) {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // 攔截原本會重整網頁的傳統送出行為
                const bodyText = input.value.trim();
                if(!bodyText) return;
 
                const formData = new FormData(form);
 
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // 告訴後端這是 AJAX 請求
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        input.value = ''; // 傳送成功後，瞬間清空輸入框，畫面不重整
                        
                        // 移除「目前尚無訊息」的預設占位字
                        if (box.querySelector('.text-center')) {
                            box.innerHTML = '';
                        }
 
                        // 在前端立即渲染「我」剛才傳出去的訊息泡泡
                        appendMessageBubble(true, '我', data.message.body, data.time);
                    }
                })
                .catch(err => console.error('訊息發送失敗:', err));
            });
        }
 
        // 💡【動態加入訊息泡泡的 HTML 函式，100% 維持你原本的精美樣式與結構】
        function appendMessageBubble(isMe, senderName, body, time) {
            const justify = isMe ? 'justify-content-end' : 'justify-content-start';
            const align = isMe ? 'align-items-end' : 'align-items-start';
            const flexRow = isMe ? 'flex-row-reverse' : 'flex-row';
            const bgClass = isMe ? 'bg-success text-white rounded-tr-0' : 'bg-light text-dark rounded-tl-0';
            const displaySender = isMe ? '我' : senderName;
 
            const html = `
                <div class="d-flex ${justify} mb-3">
                    <div class="d-flex flex-column ${align}" style="max-width: 75%;">
                        <div class="small text-muted mb-1 px-2">${displaySender}</div>
                        <div class="d-flex align-items-end gap-2 ${flexRow}">
                            <div class="p-3 rounded-4 shadow-sm ${bgClass}" style="word-break: break-all; white-space: pre-wrap;">${body}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">${time}</div>
                        </div>
                    </div>
                </div>
            `;
            box.insertAdjacentHTML('beforeend', html);
            box.scrollTop = box.scrollHeight; // 自動向下捲動置底
        }
 
        // 💡【免重整修正：即時接收廣播與即時在線已讀狀態更新】
        if (window.Echo) {
            window.Echo.private(`chat.${requestListId}`)
                .listen('MessageSent', (e) => {
                    // 【修正問題一：重複訊息錯誤】
                    // 檢查：如果這則廣播訊息是「我自己發出的」，直接 return 攔截跳過！
                    // 因為上面的 AJAX 發送成功區塊已經手動在畫面上畫過一次了，這裡阻斷它就不會再多出一則完全重複的訊息
                    if (parseInt(e.sender_id) === parseInt(currentUserId)) {
                        return;
                    }
                    
                    if (box.querySelector('.text-center')) {
                        box.innerHTML = '';
                    }
 
                    // 渲染對方即時發過來的對話泡泡到聊天室左側
                    appendMessageBubble(false, e.username, e.message, e.time);
                    
                    // 【修正問題二：在線即時已讀】
                    // 如果當下我正開著聊天視窗，對方發訊息過來時，前端會默默向後端發送一個已讀請求，資料庫瞬間刷成已讀，完全免重整頁面
                    fetch(`/request-list/${requestListId}/chat-read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).catch(err => console.log('即時已讀同步失敗:', err));
                });
        }
    });
</script>
@endsection