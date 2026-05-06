<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <section class="py-5" style="background: linear-gradient(135deg, #f0f3ff 0%, #f8f9fa 100%); min-height: calc(100vh - 80px);">
        <div class="container">
            <div class="mb-4">
                <h1 class="fw-bold mb-2" style="color:#2c3e50;">聊天訊息</h1>
                <p class="text-muted mb-0">處理來自請購人的諮詢，即時回覆報價與狀況。</p>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0" style="color:#4a6cf7;"><i class="bi bi-headset me-2"></i>GlobalBuy chat</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span id="connection-status" class="badge bg-secondary rounded-pill">連線中...</span>
                        <a href="{{ route('agent.member') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-arrow-left me-1"></i> 返回會員專區
                        </a>
                    </div>
                </div>

                <div class="card-body p-0" id="chat-app">
                    <div class="row g-0" style="min-height: 620px;">
                        <aside class="col-12 col-lg-4 col-xl-3 border-end bg-white p-3">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3 px-1">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-people-fill text-primary"></i>
                                    <span class="fw-semibold">聊天對象</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                    data-bs-toggle="modal" data-bs-target="#newChatModal">
                                    + 新增聊天
                                </button>
                            </div>
                            <div class="d-flex flex-column gap-2" id="user-list">
                                <div class="text-muted small px-2 py-4 text-center">載入中...</div>
                            </div>
                        </aside>

                        <section class="col-12 col-lg-8 col-xl-9 d-flex flex-column bg-white">
                            <div class="border-bottom px-3 px-md-4 py-3" id="chat-header">
                                <div class="text-muted">請選擇聊天對象</div>
                            </div>

                            <div id="chat-messages"
                                class="flex-grow-1 px-3 px-md-4 py-4"
                                style="background-color: #f9faff; overflow-y: auto; max-height: 430px;">
                                <div class="text-muted text-center py-5">目前沒有開啟中的聊天。</div>
                            </div>

                            <form id="chat-form" class="border-top p-3 bg-light-subtle d-flex gap-2 align-items-center">
                                @csrf
                                <input id="chat-input" type="text"
                                    class="form-control rounded-pill px-4 border-primary-subtle"
                                    placeholder="回覆請購人..." autocomplete="off" disabled>
                                <button type="submit" id="send-btn"
                                    class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2 text-nowrap"
                                    style="background-color:#4a6cf7;border:none;" disabled>
                                    <i class="bi bi-send"></i>
                                    <span>回覆</span>
                                </button>
                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 新增聊天 Modal --}}
    <div class="modal fade" id="newChatModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">新增聊天對象</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <input type="text" id="search-user-input" class="form-control mb-3 rounded-pill px-4" placeholder="搜尋用戶名稱...">
                    <div id="search-user-results" class="d-flex flex-column gap-2">
                        <p class="text-muted small">請輸入名稱搜尋。</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const MY_ID   = {{ Auth::id() }};
        const CSRF    = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const userList      = document.getElementById('user-list');
        const chatHeader    = document.getElementById('chat-header');
        const chatMessages  = document.getElementById('chat-messages');
        const chatForm      = document.getElementById('chat-form');
        const chatInput     = document.getElementById('chat-input');
        const sendBtn       = document.getElementById('send-btn');
        const statusBadge   = document.getElementById('connection-status');
        const searchInput   = document.getElementById('search-user-input');
        const searchResults = document.getElementById('search-user-results');

        let partners = [];
        let currentPartnerId = null;

        // ── Pusher 連線 ──────────────────────────────────────
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

        const channel = pusher.subscribe('private-chat.' + MY_ID);
        channel.bind('message.sent', function (data) {
            const senderId = data.senderId;
            let partner = partners.find(p => p.id === senderId);
            if (!partner) {
                partner = { id: senderId, name: data.userName, messages: [], unread: 0 };
                partners.push(partner);
            }
            partner.messages.push({
                sender: 'other', name: data.userName,
                text: data.messageContent, time: data.time,
            });
            if (currentPartnerId !== senderId) partner.unread++;
            renderUserList();
            if (currentPartnerId === senderId) renderMessages();
        });

        // ── 載入歷史對話對象 ───────────────────────────────────
        function loadPartners() {
            fetch('/messages/search-users?q=', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });

            // 從歷史訊息找出對話對象
            fetch('/messages/search-users?q=', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
            });

            const initialPartners = @json($chatPartners);
            partners = initialPartners.map(u => ({
                id: u.id, name: u.name, messages: [], unread: 0,
            }));
            renderUserList();
            if (partners.length > 0) openChat(partners[0].id);
        }

        // ── 開啟對話 ──────────────────────────────────────────
        function openChat(partnerId) {
            currentPartnerId = partnerId;
            const partner = partners.find(p => p.id === partnerId);
            if (!partner) return;
            partner.unread = 0;
            renderUserList();
            renderHeader(partner);
            enableInput(true);

            if (partner.messages.length > 0) { renderMessages(); return; }

            chatMessages.innerHTML = '<div class="text-muted text-center py-5">載入中...</div>';
            fetch(`/messages/${partnerId}/history`, {
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(messages => {
                partner.messages = messages;
                renderMessages();
            });
        }

        // ── 傳送訊息 ──────────────────────────────────────────
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const text = chatInput.value.trim();
            if (!text || !currentPartnerId) return;
            chatInput.value = '';
            sendBtn.disabled = true;

            fetch('/messages/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ receiver_id: currentPartnerId, body: text }),
            })
            .then(r => r.json())
            .then(msg => {
                const partner = partners.find(p => p.id === currentPartnerId);
                if (partner) { partner.messages.push(msg); renderMessages(); renderUserList(); }
                sendBtn.disabled = false;
                chatInput.focus();
            })
            .catch(() => { sendBtn.disabled = false; });
        });

        // ── 搜尋新用戶 ────────────────────────────────────────
        let searchTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            const q = searchInput.value.trim();
            if (!q) { searchResults.innerHTML = '<p class="text-muted small">請輸入名稱搜尋。</p>'; return; }
            searchTimer = setTimeout(() => {
                fetch(`/messages/search-users?q=${encodeURIComponent(q)}`, {
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(users => {
                    if (users.length === 0) { searchResults.innerHTML = '<p class="text-muted small">找不到用戶。</p>'; return; }
                    searchResults.innerHTML = '';
                    users.forEach(u => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'btn btn-outline-primary text-start rounded-3 d-flex justify-content-between align-items-center w-100 mb-2';
                        btn.innerHTML = `<span>${u.name}</span><span class="small opacity-75">開始聊天</span>`;
                        btn.addEventListener('click', () => {
                            if (!partners.find(p => p.id === u.id)) {
                                partners.push({ id: u.id, name: u.name, messages: [], unread: 0 });
                            }
                            bootstrap.Modal.getInstance(document.getElementById('newChatModal')).hide();
                            searchInput.value = '';
                            openChat(u.id);
                        });
                        searchResults.appendChild(btn);
                    });
                });
            }, 300);
        });

        // ── Render 函式 ────────────────────────────────────────
        function renderUserList() {
            if (partners.length === 0) {
                userList.innerHTML = '<div class="text-muted small px-2 py-4 text-center">目前沒有對話，請按「新增聊天」。</div>';
                return;
            }
            userList.innerHTML = '';
            partners.forEach(partner => {
                const isActive = partner.id === currentPartnerId;
                const lastMsg = partner.messages[partner.messages.length - 1]?.text ?? '';
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `btn text-start border rounded-4 p-3 w-100 mb-1 ${isActive ? 'border-primary-subtle bg-primary-subtle' : 'bg-white shadow-sm'}`;
                btn.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="d-flex gap-2 align-items-center min-w-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold border"
                                style="width:42px;height:42px;flex-shrink:0;${isActive ? 'background:#4a6cf7;color:#fff;' : 'background:#eee;color:#666;'}">
                                ${partner.name.slice(0,1)}
                            </div>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark text-truncate">${partner.name}</div>
                                <div class="text-muted small text-truncate" style="max-width:125px;">${lastMsg}</div>
                            </div>
                        </div>
                        ${partner.unread > 0 ? `<span class="badge rounded-pill bg-danger">${partner.unread}</span>` : ''}
                    </div>
                `;
                btn.addEventListener('click', () => openChat(partner.id));
                userList.appendChild(btn);
            });
        }

        function renderHeader(partner) {
            chatHeader.innerHTML = `
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm"
                        style="width:46px;height:46px;background:#4a6cf7;flex-shrink:0;">${partner.name.slice(0,1)}</div>
                    <div>
                        <div class="fw-bold" style="color:#2c3e50;">${partner.name}</div>
                        <div class="text-muted small">請購人</div>
                    </div>
                </div>
            `;
        }

        function renderMessages() {
            const partner = partners.find(p => p.id === currentPartnerId);
            chatMessages.innerHTML = '';
            if (!partner || partner.messages.length === 0) {
                chatMessages.innerHTML = '<div class="text-muted text-center py-5">還沒有任何訊息，傳送第一則訊息吧！</div>';
                return;
            }
            partner.messages.forEach(msg => {
                const isMe = msg.sender === 'me';
                const row = document.createElement('div');
                row.className = `d-flex mb-3 ${isMe ? 'justify-content-end' : 'justify-content-start'}`;

                const wrap = document.createElement('div');
                wrap.className = `d-flex flex-column ${isMe ? 'align-items-end' : 'align-items-start'}`;

                const topRow = document.createElement('div');
                topRow.className = `d-flex align-items-end gap-2 ${isMe ? 'flex-row-reverse' : ''}`;

                const avatar = document.createElement('div');
                avatar.className = 'rounded-circle border d-flex align-items-center justify-content-center fw-bold text-secondary bg-white';
                avatar.style.cssText = 'width:34px;height:34px;font-size:11px;flex-shrink:0;';
                avatar.textContent = msg.name.slice(0,1);

                const bubble = document.createElement('div');
                bubble.className = 'px-3 py-2 rounded-3 border fw-semibold shadow-sm';
                bubble.style.cssText = `max-width:300px;overflow-wrap:anywhere;word-break:break-word;line-height:1.5;background:${isMe ? '#eef2ff' : '#fff'};border-color:${isMe ? '#dbe4ff' : '#dee2e6'};color:#2c3e50;`;
                bubble.textContent = msg.text;

                topRow.appendChild(avatar);
                topRow.appendChild(bubble);
                wrap.appendChild(topRow);

                const time = document.createElement('div');
                time.className = `small text-muted mt-1 px-1 ${isMe ? 'text-end' : 'text-start'}`;
                time.textContent = msg.time || '--:--';
                wrap.appendChild(time);

                row.appendChild(wrap);
                chatMessages.appendChild(row);
            });
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function enableInput(enabled) {
            chatInput.disabled = !enabled;
            sendBtn.disabled = !enabled;
            if (enabled) chatInput.focus();
        }

        loadPartners();
    });
    </script>
</x-app-layout>