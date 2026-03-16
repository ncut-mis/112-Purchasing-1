@extends('layouts.front')

@section('content')
<section class="py-5" style="background: linear-gradient(135deg, #f0f3ff 0%, #f8f9fa 100%); min-height: calc(100vh - 80px);">
    <div class="container">
        <div class="mb-4">
            <h1 class="fw-bold mb-2" style="color:#2c3e50;">代購訂單管理：聊天室</h1>
            <p class="text-muted mb-0">處理來自請購人的諮詢，即時回覆報價與庫存狀況。</p>
        </div>

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0" style="color:#4a6cf7;"><i class="bi bi-headset me-2"></i>Agent Console</h5>
                <a href="{{ route('agent.member') }}" class="flex items-center gap-2 px-4 py-2 bg-white border border-indigo-100 rounded-full text-sm font-bold text-indigo-600 hover:bg-indigo-50 transition shadow-sm no-underline">
                    <i class="bi bi-arrow-left"></i>
                    <span>返回管理後台</span>
                </a>
            </div>

            <div class="card-body p-0" id="chat-app">
                <div class="row g-0" style="min-height: 620px;">
                    <aside class="col-12 col-lg-4 col-xl-3 border-end bg-white p-3">
                        <div class="d-flex align-items-center gap-2 mb-3 px-1">
                            <i class="bi bi-chat-quote-fill text-primary"></i>
                            <span class="fw-semibold">請購人訊息清單</span>
                        </div>
                        <div class="d-flex flex-column gap-2" id="user-list"></div>
                    </aside>

                    <section class="col-12 col-lg-8 col-xl-9 d-flex flex-column bg-white">
                        <div class="border-bottom px-3 px-md-4 py-3" id="chat-header"></div>

                        <div
                            id="chat-messages"
                            class="flex-grow-1 px-3 px-md-4 py-4"
                            style="background-color: #f9faff; overflow-y: auto; max-height: 430px;"
                        ></div>

                        <form id="chat-form" class="border-top p-3 bg-light-subtle d-flex gap-2 align-items-center">
                            <input
                                id="chat-input"
                                type="text"
                                class="form-control rounded-pill px-4 border-primary-subtle"
                                placeholder="回覆請購人..."
                                autocomplete="off"
                            >
                            <button type="submit" class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2 text-nowrap" style="background-color: #4a6cf7;">
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 核心資料狀態 (代購人視角) ---
        const state = {
            currentUserId: 101, // 當前選中的請購人 ID
            // 這裡模擬從資料庫抓取的對話記錄
            users: [
                {
                    id: 101,
                    name: '請購人 A1',
                    intro: '詢問中：大正百龍館、奇巧巧克力',
                    unread: 0,
                    isVisible: true,
                    messages: [
                        { sender: 'other', name: 'A1', text: '請問價錢可以談談嗎？', time: '09:18' },
                        { sender: 'me', name: 'Agent', text: '抱歉，這些商品目前價格固定喔。', time: '09:20' },
                        { sender: 'other', name: 'A1', text: '好的謝謝，那我再考慮一下。', time: '09:21' },
                    ],
                },
                {
                    id: 102,
                    name: '請購人 B2',
                    intro: '詢問中：日本藥妝代購',
                    unread: 2,
                    isVisible: true,
                    messages: [
                        { sender: 'other', name: 'B2', text: '請問這個還有貨嗎？', time: '11:00' },
                    ],
                },
            ],
        };

        const userList = document.getElementById('user-list');
        const chatHeader = document.getElementById('chat-header');
        const chatMessages = document.getElementById('chat-messages');
        const chatForm = document.getElementById('chat-form');
        const chatInput = document.getElementById('chat-input');

        function getCurrentUser() {
            return state.users.find((u) => u.id === state.currentUserId) || null;
        }

        // 渲染請購人列表
        function renderUsers() {
            userList.innerHTML = '';
            state.users.forEach((user) => {
                const isActive = user.id === state.currentUserId;
                const button = document.createElement('button');
                button.className = `btn text-start border rounded-4 p-3 mb-2 w-100 ${isActive ? 'border-primary-subtle bg-primary-subtle' : 'bg-white shadow-sm'}`;

                button.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2 align-items-center overflow-hidden">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold border" 
                                 style="width:40px;height:40px; flex-shrink:0; ${isActive ? 'background:#4a6cf7;color:#fff;' : 'background:#eee;color:#666;'}">
                                ${user.name.charAt(user.name.length - 1)}
                            </div>
                            <div class="text-truncate">
                                <div class="fw-bold text-dark">${user.name}</div>
                                <div class="text-muted small text-truncate">${user.intro}</div>
                            </div>
                        </div>
                        ${user.unread > 0 ? `<span class="badge rounded-pill bg-danger">${user.unread}</span>` : ''}
                    </div>
                `;

                button.onclick = () => {
                    state.currentUserId = user.id;
                    user.unread = 0;
                    render();
                };
                userList.appendChild(button);
            });
        }

        // 渲染對話標頭
        function renderHeader() {
            const user = getCurrentUser();
            if (!user) return;
            chatHeader.innerHTML = `
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white" 
                         style="width:46px;height:46px;background:#4a6cf7;">${user.name.charAt(user.name.length - 1)}</div>
                    <div>
                        <div class="fw-bold">${user.name}</div>
                        <div class="text-muted small"><span class="badge bg-light text-primary border border-primary-subtle">請購中</span> ${user.intro}</div>
                    </div>
                </div>
            `;
        }

        // 渲染訊息泡泡 (邏輯與請購人端一致，但顏色改為藍色系)
        function renderMessages() {
            const user = getCurrentUser();
            chatMessages.innerHTML = '';
            if (!user) return;

            user.messages.forEach((msg) => {
                const isMe = msg.sender === 'me';
                const row = document.createElement('div');
                row.className = `d-flex mb-3 ${isMe ? 'justify-content-end' : 'justify-content-start'}`;

                row.innerHTML = `
                    <div class="d-flex flex-column ${isMe ? 'align-items-end' : 'align-items-start'}" style="max-width: 75%;">
                        <div class="d-flex align-items-end gap-2 ${isMe ? 'flex-row-reverse' : ''}">
                            <div class="rounded-circle border d-flex align-items-center justify-content-center fw-bold bg-white text-secondary" 
                                 style="width:32px;height:32px;font-size:10px;">${msg.name}</div>
                            <div class="px-3 py-2 rounded-3 border shadow-sm" 
                                 style="background-color: ${isMe ? '#eef2ff' : '#ffffff'}; border-color: #dee2e6 !important;">
                                ${msg.text}
                            </div>
                        </div>
                        <div class="small text-muted mt-1 px-1">${msg.time}</div>
                    </div>
                `;
                chatMessages.appendChild(row);
            });
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function formatTime() {
            const now = new Date();
            return `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
        }

        chatForm.onsubmit = (e) => {
            e.preventDefault();
            const text = chatInput.value.trim();
            if (!text) return;

            const user = getCurrentUser();
            // 在代購人端，送出者標記為 'Agent'
            user.messages.push({ sender: 'me', name: 'Agent', text, time: formatTime() });
            chatInput.value = '';
            render();
        };

        function render() {
            renderUsers();
            renderHeader();
            renderMessages();
        }

        render();
    });
</script>
@endsection