@extends('layouts.front')

@section('content')
<section class="py-5" style="background: linear-gradient(135deg, #e9f6f4 0%, #f3f7f5 100%); min-height: calc(100vh - 80px);">
    <div class="container">
        <div class="mb-4">
            <h1 class="fw-bold mb-2" style="color:#2c3e50;">聊天訊息</h1>
            <p class="text-muted mb-0">與不同代購人切換聊天，並可即時輸入與傳送訊息。</p>
        </div>

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> 返回 Dashboard
                </a>
            </div>

            <div class="card-body p-0" id="chat-app">
                <div class="row g-0" style="min-height: 620px;">
                    <aside class="col-12 col-lg-4 col-xl-3 border-end bg-white p-3">
                        <div class="d-flex align-items-center gap-2 mb-3 px-1">
                            <i class="bi bi-people-fill text-success"></i>
                            <span class="fw-semibold">聊天對象</span>
                        </div>
                        <div class="d-flex flex-column gap-2" id="user-list"></div>
                    </aside>

                    <section class="col-12 col-lg-8 col-xl-9 d-flex flex-column bg-white">
                        <div class="border-bottom px-3 px-md-4 py-3" id="chat-header"></div>

                        <div
                            id="chat-messages"
                            class="flex-grow-1 px-3 px-md-4 py-4"
                            style="background-color: #f8fbfa; overflow-y: auto; max-height: 430px;"
                        ></div>

                        <form id="chat-form" class="border-top p-3 bg-light-subtle d-flex gap-2 align-items-center">
                            <input
                                id="chat-input"
                                type="text"
                                class="form-control rounded-pill px-4"
                                placeholder="請輸入訊息"
                                autocomplete="off"
                            >
                            <button type="submit" class="btn btn-primary-custom rounded-pill px-4 d-flex align-items-center gap-2 text-nowrap">
                                <i class="bi bi-send"></i>
                                <span>傳送</span>
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
        const state = {
            currentUserId: 1,
            users: [
                {
                    id: 1,
                    name: 'Entity',
                    intro: '代購人 Entity：大正百龍館、烏龍奇娃娃、kitkat奇巧巧克力',
                    unread: 1,
                    messages: [
                        { sender: 'me', name: 'A1', text: '請問價錢可以談談嗎？' },
                        { sender: 'other', name: 'Entity', text: '抱歉價格固定可能不行喔' },
                        { sender: 'me', name: 'A1', text: '好的謝謝' },
                    ],
                },
                {
                    id: 2,
                    name: 'Cryene',
                    intro: '代購人 Cryene：大正百龍館、烏龍奇娃娃、kitkat奇巧巧克力',
                    unread: 2,
                    messages: [
                        { sender: 'me', name: 'A1', text: '請問巧克力價錢可以談談嗎？' },
                        { sender: 'other', name: 'Cryene', text: '目前巧克力價格單價60,可微調' },
                        { sender: 'me', name: 'A1', text: '可以降價為45嗎？' },
                        { sender: 'other', name: 'Cryene', text: '可以喔ww' },
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
            return state.users.find((u) => u.id === state.currentUserId);
        }

        function renderUsers() {
            userList.innerHTML = '';

            state.users.forEach((user) => {
                const isActive = user.id === state.currentUserId;
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `btn text-start border rounded-4 p-3 ${isActive ? 'border-success-subtle bg-success-subtle' : 'bg-white hover-shadow-sm'}`;

                button.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="d-flex gap-2 align-items-center min-w-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold border" style="width:42px;height:42px;${isActive ? 'background:#d7f2ea;color:#3d8e7f;' : 'background:#fff;color:#6c757d;'}">
                                ${user.name.slice(0, 1)}
                            </div>
                            <div class="min-w-0">
                                <div class="fw-semibold text-dark">${user.name}</div>
                                <div class="text-muted small text-truncate" style="max-width: 160px;">${user.messages[user.messages.length - 1]?.text ?? ''}</div>
                            </div>
                        </div>
                        ${user.unread > 0 ? `<span class="badge rounded-pill text-bg-danger">${user.unread}</span>` : ''}
                    </div>
                `;

                button.addEventListener('click', () => {
                    state.currentUserId = user.id;
                    user.unread = 0;
                    render();
                });

                userList.appendChild(button);
            });
        }

        function renderHeader() {
            const user = getCurrentUser();
            chatHeader.innerHTML = `
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold border" style="width:46px;height:46px;background:#e8f5f2;color:#3d8e7f;">${user.name}</div>
                    <div>
                        <div class="fw-bold" style="color:#2c3e50;">${user.name}</div>
                        <div class="text-muted small">${user.intro}</div>
                    </div>
                </div>
            `;
        }

        function renderMessages() {
            const user = getCurrentUser();
            chatMessages.innerHTML = '';

            user.messages.forEach((message) => {
                const row = document.createElement('div');
                row.className = `d-flex mb-3 ${message.sender === 'me' ? 'justify-content-end' : 'justify-content-start'}`;

                const bubbleWrap = document.createElement('div');
                bubbleWrap.className = 'd-flex align-items-end gap-2';

                if (message.sender !== 'me') {
                    const avatar = document.createElement('div');
                    avatar.className = 'rounded-circle border d-flex align-items-center justify-content-center fw-bold text-secondary bg-white';
                    avatar.style.width = '34px';
                    avatar.style.height = '34px';
                    avatar.style.fontSize = '11px';
                    avatar.textContent = message.name;
                    bubbleWrap.appendChild(avatar);
                }

                const bubble = document.createElement('div');
                bubble.className = 'px-3 py-2 rounded-3 border fw-semibold';
                bubble.style.maxWidth = '75%';
                bubble.style.backgroundColor = message.sender === 'me' ? '#e7f5f1' : '#ffffff';
                bubble.style.borderColor = '#d7e3df';
                bubble.style.color = '#2c3e50';
                bubble.textContent = message.text;
                bubbleWrap.appendChild(bubble);

                if (message.sender === 'me') {
                    const avatar = document.createElement('div');
                    avatar.className = 'rounded-circle border d-flex align-items-center justify-content-center fw-bold text-secondary bg-white';
                    avatar.style.width = '34px';
                    avatar.style.height = '34px';
                    avatar.style.fontSize = '11px';
                    avatar.textContent = message.name;
                    bubbleWrap.appendChild(avatar);
                }

                row.appendChild(bubbleWrap);
                chatMessages.appendChild(row);
            });

            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function render() {
            renderUsers();
            renderHeader();
            renderMessages();
        }

        chatForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const text = chatInput.value.trim();

            if (!text) {
                return;
            }

            const user = getCurrentUser();
            user.messages.push({ sender: 'me', name: 'A1', text });
            chatInput.value = '';
            render();
        });

        render();
    });
</script>
@endsection