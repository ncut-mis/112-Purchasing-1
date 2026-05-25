@extends('layouts.front')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<section class="py-5" style="background: linear-gradient(135deg, #e9f6f4 0%, #f3f7f5 100%); min-height: calc(100vh - 80px);">
    <div class="container" style="max-width: 860px;">

        {{-- 返回按鈕 --}}
        <div class="mb-4 d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div>
                <h1 class="fw-bold mb-1" style="color:#2c3e50;">請購清單聊天室</h1>
                <p class="text-muted mb-0">此對話僅限該請購單的請購人與接單代購人可見。</p>
            </div>
            <a href="{{ $isBuyer ? route('dashboard') : route('agent.dashboard') }}"
               class="btn btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center"
               style="width:44px;height:44px;" aria-label="關閉聊天室">✕</a>
        </div>

        {{-- 請購單資訊卡 --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                    style="width:48px;height:48px;background:linear-gradient(135deg,#6366f1,#8b5cf6);flex-shrink:0;">
                    <i class="bi bi-bag-check"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="fw-bold text-dark">{{ $requestList->title }}</div>
                    <div class="text-muted small">
                        {{ $isBuyer ? '你的請購單' : '請購人：' . $requestList->user->name }}
                        &nbsp;·&nbsp;
                        截止：{{ optional($requestList->deadline)->format('Y/m/d') ?? '-' }}
                        &nbsp;·&nbsp;
                        <span class="badge rounded-pill"
                            style="background:{{ $requestList->status === 'matched' ? '#198754' : '#ffc107' }};color:{{ $requestList->status === 'matched' ? '#fff' : '#000' }}">
                            {{ ['pending' => '待接單', 'offered' => '已報價', 'matched' => '已確認', 'wait-for-ship' => '等待出貨', 'shipped' => '已出貨', 'arrivaled' => '已到貨', 'expired' => '已過期'][$requestList->status] ?? $requestList->status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- 聊天主體 --}}
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            {{-- Header --}}
            <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold border"
                    style="width:46px;height:46px;background:#e8f5f2;color:#3d8e7f;flex-shrink:0;">
                    {{ mb_substr($partner->name, 0, 1) }}
                </div>
                <div>
                    <div class="fw-bold" style="color:#2c3e50;">{{ $partner->name }}</div>
                    <div class="text-muted small">{{ $isBuyer ? '代購人' : '請託人' }}</div>
                </div>
                <div class="ms-auto">
                    <span id="connection-status" class="badge bg-secondary rounded-pill">連線中...</span>
                </div>
            </div>

            {{-- 訊息區 --}}
            <div id="chat-messages"
                class="px-4 py-4"
                style="background:#f8fbfa; overflow-y:auto; min-height:420px; max-height:420px;">
                @if(count($messages) === 0)
                    <div class="text-muted text-center py-5" id="empty-tip">還沒有任何訊息，傳送第一則訊息吧！</div>
                @else
                    @foreach($messages as $i => $msg)
                        @php
                            $isMe = $msg['sender'] === 'me';
                            $isLast = $i === array_key_last($messages->toArray());
                        @endphp
                        <div class="d-flex mb-3 {{ $isMe ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="d-flex flex-column {{ $isMe ? 'align-items-end' : 'align-items-start' }}">
                                <div class="d-flex align-items-end gap-2 {{ $isMe ? 'flex-row-reverse' : '' }}">
                                    <div class="rounded-circle border d-flex align-items-center justify-content-center fw-bold text-secondary bg-white"
                                        style="width:34px;height:34px;font-size:11px;flex-shrink:0;">
                                        {{ mb_substr($msg['name'], 0, 1) }}
                                    </div>
                                    <div class="px-3 py-2 rounded-3 border fw-semibold"
                                        style="max-width:300px;overflow-wrap:anywhere;word-break:break-word;background:{{ $isMe ? '#e7f5f1' : '#fff' }};border-color:#d7e3df;color:#2c3e50;line-height:1.5;">
                                        {{ $msg['text'] }}
                                    </div>
                                </div>
                                <div class="small text-muted mt-1 px-1 d-flex align-items-center gap-1 {{ $isMe ? 'justify-content-end' : '' }}">
                                    <span>{{ $msg['time'] }}</span>
                                    @if($isMe && $isLast)
                                        <span class="msg-read-status" style="color:{{ isset($msg['read_at']) && $msg['read_at'] ? '#3d8e7f' : '#aaa' }}">
                                            {{ isset($msg['read_at']) && $msg['read_at'] ? '已讀' : '未讀' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- 輸入區 --}}
            <div id="chat-form-wrap" class="border-top p-3 bg-light d-flex gap-2 align-items-center">
                <input id="chat-input" type="text" class="form-control rounded-pill px-4"
                    placeholder="請輸入訊息..." autocomplete="off" maxlength="1000">
                <button type="button" id="send-btn"
                    class="btn btn-success rounded-pill px-4 d-flex align-items-center gap-2 text-nowrap">
                    <i class="bi bi-send"></i>
                    <span>傳送</span>
                </button>
            </div>
        </div>

    </div>
</section>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const MY_ID      = {{ Auth::id() }};
    const PARTNER_ID = {{ $partner->id }};
    const LIST_ID    = {{ $requestList->id }};
    const CSRF       = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const SEND_URL   = '{{ route('request-list.chat.send', $requestList) }}';
    const READ_URL   = '{{ route('request-list.chat.read', $requestList) }}';

    const chatMessages = document.getElementById('chat-messages');
    const chatInput    = document.getElementById('chat-input');
    const sendBtn      = document.getElementById('send-btn');
    const statusBadge  = document.getElementById('connection-status');

    // ── 捲到底 ──────────────────────────────────────────────────
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    scrollToBottom();

    // 進入頁面標記已讀
    fetch(READ_URL, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    }).catch(() => {});

    // ── 新增訊息泡泡 ─────────────────────────────────────────────
    function appendMessage(msg) {
        const emptyTip = document.getElementById('empty-tip');
        if (emptyTip) emptyTip.remove();

        const isMe = msg.sender === 'me';
        // 新訊息加入前，移除舊的已讀標示（自己的訊息才移）
        if (isMe) {
            chatMessages.querySelectorAll('.msg-read-status').forEach(el => el.remove());
        }

        const row = document.createElement('div');
        row.className = `d-flex mb-3 ${isMe ? 'justify-content-end' : 'justify-content-start'}`;
        row.innerHTML = `
            <div class="d-flex flex-column ${isMe ? 'align-items-end' : 'align-items-start'}">
                <div class="d-flex align-items-end gap-2 ${isMe ? 'flex-row-reverse' : ''}">
                    <div class="rounded-circle border d-flex align-items-center justify-content-center fw-bold text-secondary bg-white"
                        style="width:34px;height:34px;font-size:11px;flex-shrink:0;">
                        ${msg.name.slice(0,1)}
                    </div>
                    <div class="px-3 py-2 rounded-3 border fw-semibold"
                        style="max-width:300px;overflow-wrap:anywhere;word-break:break-word;
                               background:${isMe ? '#e7f5f1' : '#fff'};border-color:#d7e3df;color:#2c3e50;line-height:1.5;">
                        ${msg.text}
                    </div>
                </div>
                <div class="small text-muted mt-1 px-1 d-flex align-items-center gap-1 ${isMe ? 'justify-content-end' : ''}">
                    <span>${msg.time}</span>
                    ${isMe ? '<span class="msg-read-status" style="color:#aaa">未讀</span>' : ''}
                </div>
            </div>
        `;
        chatMessages.appendChild(row);
        scrollToBottom();
    }

    // ── Pusher 連線 ──────────────────────────────────────────────
    const pusher = new Pusher('{{ config("broadcasting.connections.pusher.key") }}', {
        cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
        forceTLS: true,
        authEndpoint: '/broadcasting/auth',
        auth: { headers: { 'X-CSRF-TOKEN': CSRF } }
    });

    pusher.connection.bind('connected', () => {
        statusBadge.textContent = '已連線';
        statusBadge.className = 'badge bg-success rounded-pill';
    });
    pusher.connection.bind('disconnected', () => {
        statusBadge.textContent = '已斷線';
        statusBadge.className = 'badge bg-danger rounded-pill';
    });

    // 訂閱自己的私人頻道
    const channel = pusher.subscribe('private-chat.' + MY_ID);

    // 收到新訊息
    channel.bind('message.sent', function (data) {
        // 只處理這張請託單的訊息，且必須是對方發來的
        if (data.requestListId !== LIST_ID) return;
        if (data.senderId !== PARTNER_ID) return;

        appendMessage({
            sender: 'other',
            name:   data.userName,
            text:   data.messageContent,
            time:   data.time,
        });

        // 自動標記已讀並廣播給對方
        fetch(READ_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        }).catch(() => {});
    });

    // 收到已讀通知（對方讀了我的訊息）
    channel.bind('message.read', function (data) {
        if (data.readerId !== PARTNER_ID) return;
        if (data.requestListId !== LIST_ID) return;

        chatMessages.querySelectorAll('.msg-read-status').forEach(el => {
            el.style.color = '#3d8e7f';
            el.textContent = '已讀';
        });
    });

    // ── 傳送訊息 ─────────────────────────────────────────────────
    function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;

        chatInput.value = '';
        sendBtn.disabled = true;

        fetch(SEND_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ body: text, receiver_id: PARTNER_ID }),
        })
        .then(r => r.json())
        .then(msg => {
            appendMessage(msg);
            sendBtn.disabled = false;
            chatInput.focus();
        })
        .catch(() => { sendBtn.disabled = false; });
    }

    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    chatInput.focus();
});
</script>
@endsection