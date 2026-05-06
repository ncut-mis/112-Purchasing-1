<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <section class="py-5" style="background: linear-gradient(135deg, #e9f6f4 0%, #f3f7f5 100%); min-height: calc(100vh - 80px);">
        <div class="container" style="max-width: 860px;">

            {{-- 返回按鈕 --}}
            <div class="mb-4">
                <a href="{{ $isBuyer ? route('dashboard') : route('agent.dashboard') }}"
                   class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> 返回
                </a>
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
                            <span class="badge bg-{{ $requestList->status === 'matched' ? 'success' : 'warning text-dark' }} rounded-pill">
                                {{ ['pending' => '待接單', 'offered' => '已報價', 'matched' => '已確認', 'completed' => '已完成'][$requestList->status] ?? $requestList->status }}
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
                        @foreach($messages as $msg)
                            @php $isMe = $msg['sender'] === 'me'; @endphp
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
                                    <div class="small text-muted mt-1 px-1 {{ $isMe ? 'text-end' : 'text-start' }}">
                                        {{ $msg['time'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- 輸入區 --}}
                <form id="chat-form" class="border-top p-3 bg-light d-flex gap-2 align-items-center">
                    @csrf
                    <input id="chat-input" type="text" class="form-control rounded-pill px-4"
                        placeholder="請輸入訊息..." autocomplete="off">
                    <button type="submit" id="send-btn"
                        class="btn btn-success rounded-pill px-4 d-flex align-items-center gap-2 text-nowrap">
                        <i class="bi bi-send"></i>
                        <span>傳送</span>
                    </button>
                </form>
            </div>

        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const MY_ID      = {{ Auth::id() }};
        const PARTNER_ID = {{ $partner->id }};
        const LIST_ID    = {{ $requestList->id }};
        const CSRF       = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const SEND_URL   = '{{ route('request-list.chat.send', $requestList) }}';

        const chatMessages = document.getElementById('chat-messages');
        const chatForm     = document.getElementById('chat-form');
        const chatInput    = document.getElementById('chat-input');
        const sendBtn      = document.getElementById('send-btn');
        const statusBadge  = document.getElementById('connection-status');
        const emptyTip     = document.getElementById('empty-tip');

        // ── 捲到底 ──────────────────────────────────────────
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        scrollToBottom();

        // ── 新增訊息泡泡 ─────────────────────────────────────
        function appendMessage(msg) {
            if (emptyTip) emptyTip.remove();

            const isMe = msg.sender === 'me';
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
                    <div class="small text-muted mt-1 px-1 ${isMe ? 'text-end' : 'text-start'}">${msg.time}</div>
                </div>
            `;
            chatMessages.appendChild(row);
            scrollToBottom();
        }

        // ── Pusher 連線 ──────────────────────────────────────
        const pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
            cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
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
        channel.bind('message.sent', function (data) {
            // 只顯示這張請購單的訊息
            if (data.senderId === PARTNER_ID) {
                appendMessage({
                    sender: 'other',
                    name:   data.userName,
                    text:   data.messageContent,
                    time:   data.time,
                });
            }
        });

        // ── 傳送訊息 ─────────────────────────────────────────
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
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
                body: JSON.stringify({ body: text }),
            })
            .then(r => r.json())
            .then(msg => {
                appendMessage(msg);
                sendBtn.disabled = false;
                chatInput.focus();
            })
            .catch(() => { sendBtn.disabled = false; });
        });

        chatInput.focus();
    });
    </script>
</x-app-layout>