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
                <h5 class="fw-bold mb-0" style="color:#2c3e50;">GlobalBuy Chat</h5>
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> 返回 Dashboard
                </a>
            </div>

            <div class="card-body p-0" id="chat-app">
                <div class="row g-0" style="min-height: 620px;">
                    <aside class="col-12 col-lg-4 col-xl-3 border-end bg-white p-3">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3 px-1">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-people-fill text-success"></i>
                                <span class="fw-semibold">聊天對象</span>
                            </div>
                            <button id="restore-chat-btn" type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#restoreChatModal">
                                + 新增聊天
                            </button>
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

<!-- 關閉聊天確認視窗 -->
<div class="modal fade" id="closeChatModal" tabindex="-1" aria-labelledby="closeChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="closeChatModalLabel">提示</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                是否關閉聊天訊息
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">否</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" id="confirm-close-chat">是</button>
            </div>
        </div>
    </div>
</div>

<!-- 重新開啟聊天視窗 -->
<div class="modal fade" id="restoreChatModal" tabindex="-1" aria-labelledby="restoreChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="restoreChatModalLabel">新增聊天對象</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div id="restore-user-list" class="d-flex flex-column gap-2"></div>
                <p id="restore-empty-tip" class="text-muted mb-0 d-none">目前沒有可新增的聊天對象。</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const state = {
            currentUserId: 1,
            pendingCloseUserId: null,
            users: [
                {
                    id: 1,
                    name: 'Entity',
                    intro: '代購人 Entity：大正百龍館、烏龍奇娃娃、kitkat奇巧巧克力',
                    unread: 1,
                    isVisible: true,
                    messages: [
                        { sender: 'me', name: 'A1', text: '請問價錢可以談談嗎？', time: '09:18' },
                        { sender: 'other', name: 'Entity', text: '抱歉價格固定可能不行喔', time: '09:20' },
                        { sender: 'me', name: 'A1', text: '好的謝謝', time: '09:21' },
                    ],
                },
                {
                    id: 2,
                    name: 'Cryene',
                    intro: '代購人 Cryene：大正百龍館、烏龍奇娃娃、kitkat奇巧巧克力',
                    unread: 2,
                    isVisible: true,
                    messages: [
                        { sender: 'me', name: 'A1', text: '請問巧克力價錢可以談談嗎？', time: '10:02' },
                        { sender: 'other', name: 'Cryene', text: '目前巧克力價格單價60,可微調', time: '10:05' },
                        { sender: 'me', name: 'A1', text: '可以降價為45嗎？', time: '10:07' },
                        { sender: 'other', name: 'Cryene', text: '可以喔ww', time: '10:08' },
                    ],
                },
            ],
        };

        const userList = document.getElementById('user-list');
        const chatHeader = document.getElementById('chat-header');
        const chatMessages = document.getElementById('chat-messages');
        const chatForm = document.getElementById('chat-form');
        const chatInput = document.getElementById('chat-input');
        const confirmCloseBtn = document.getElementById('confirm-close-chat');
        const restoreUserList = document.getElementById('restore-user-list');
        const restoreEmptyTip = document.getElementById('restore-empty-tip');

        const closeModalElement = document.getElementById('closeChatModal');
        const closeChatModal = new bootstrap.Modal(closeModalElement);
        const restoreModalElement = document.getElementById('restoreChatModal');
        const restoreChatModal = new bootstrap.Modal(restoreModalElement);

        function getVisibleUsers() {
            return state.users.filter((u) => u.isVisible);
        }

        function getHiddenUsers() {
            return state.users.filter((u) => !u.isVisible);
        }

        function getCurrentUser() {
            return state.users.find((u) => u.id === state.currentUserId) || null;
        }

        function ensureCurrentUser() {
            const current = getCurrentUser();
            if (!current || !current.isVisible) {
                const firstVisible = getVisibleUsers()[0];
                state.currentUserId = firstVisible ? firstVisible.id : null;
            }
        }

        function renderUsers() {
            userList.innerHTML = '';
            const visibleUsers = getVisibleUsers();

            if (visibleUsers.length === 0) {
                userList.innerHTML = '<div class="text-muted small px-2 py-4">目前沒有開啟中的聊天，請按「新增聊天」。</div>';
                return;
            }

            visibleUsers.forEach((user) => {
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
                                <div class="text-muted small text-truncate" style="max-width: 125px;">${user.messages[user.messages.length - 1]?.text ?? ''}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            ${user.unread > 0 ? `<span class="badge rounded-pill text-bg-danger">${user.unread}</span>` : ''}
                            <span
                                class="text-muted fw-bold"
                                role="button"
                                title="關閉聊天"
                                data-close-user-id="${user.id}"
                                style="line-height:1; font-size:18px;"
                            >×</span>
                        </div>
                    </div>
                `;

                button.addEventListener('click', (event) => {
                    if (event.target.closest('[data-close-user-id]')) {
                        return;
                    }
                    state.currentUserId = user.id;
                    user.unread = 0;
                    render();
                });

                userList.appendChild(button);
            });

            userList.querySelectorAll('[data-close-user-id]').forEach((closeBtn) => {
                closeBtn.addEventListener('click', (event) => {
                    event.stopPropagation();
                    state.pendingCloseUserId = Number(closeBtn.getAttribute('data-close-user-id'));
                    closeChatModal.show();
                });
            });
        }

        function renderHeader() {
            const user = getCurrentUser();
            if (!user) {
                chatHeader.innerHTML = '<div class="text-muted">尚未選擇聊天對象</div>';
                return;
            }

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

        function formatCurrentTime() {
            const now = new Date();
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            return `${hh}:${mm}`;
        }

        function renderMessages() {
            const user = getCurrentUser();
            chatMessages.innerHTML = '';

            if (!user) {
                chatMessages.innerHTML = '<div class="text-muted">目前沒有開啟中的聊天。</div>';
                return;
            }

            user.messages.forEach((message) => {
                const row = document.createElement('div');
                row.className = `d-flex mb-3 ${message.sender === 'me' ? 'justify-content-end' : 'justify-content-start'}`;

                const bubbleWrap = document.createElement('div');
                bubbleWrap.className = `d-flex flex-column ${message.sender === 'me' ? 'align-items-end' : 'align-items-start'}`;

                const topRow = document.createElement('div');
                topRow.className = `d-flex align-items-end gap-2 ${message.sender === 'me' ? 'flex-row-reverse' : ''}`;

                const bubble = document.createElement('div');
                bubble.className = 'px-3 py-2 rounded-3 border fw-semibold';
                bubble.style.display = 'inline-block';
                bubble.style.width = 'fit-content';
                bubble.style.maxWidth = '18ch';
                bubble.style.whiteSpace = 'normal';
                bubble.style.overflowWrap = 'anywhere';
                bubble.style.wordBreak = 'break-all';
                bubble.style.backgroundColor = message.sender === 'me' ? '#e7f5f1' : '#ffffff';
                bubble.style.borderColor = '#d7e3df';
                bubble.style.color = '#2c3e50';
                bubble.style.lineHeight = '1.5';
                bubble.textContent = message.text;

                const avatar = document.createElement('div');
                avatar.className = 'rounded-circle border d-flex align-items-center justify-content-center fw-bold text-secondary bg-white';
                avatar.style.width = '34px';
                avatar.style.height = '34px';
                avatar.style.fontSize = '11px';
                avatar.textContent = message.name;

                topRow.appendChild(avatar);
                topRow.appendChild(bubble);
                bubbleWrap.appendChild(topRow);

                const timeNode = document.createElement('div');
                timeNode.className = `small text-muted mt-1 px-1 ${message.sender === 'me' ? 'text-end' : 'text-start'}`;
                timeNode.textContent = message.time || '--:--';
                bubbleWrap.appendChild(timeNode);

                row.appendChild(bubbleWrap);
                chatMessages.appendChild(row);
            });

            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function renderRestoreList() {
            restoreUserList.innerHTML = '';
            const hiddenUsers = getHiddenUsers();

            if (hiddenUsers.length === 0) {
                restoreEmptyTip.classList.remove('d-none');
                return;
            }

            restoreEmptyTip.classList.add('d-none');
            hiddenUsers.forEach((user) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-success text-start rounded-3 d-flex justify-content-between align-items-center';
                btn.innerHTML = `
                    <span>${user.name}</span>
                    <span class="small text-muted">恢復聊天</span>
                `;
                btn.addEventListener('click', () => {
                    user.isVisible = true;
                    state.currentUserId = user.id;
                    render();
                    restoreChatModal.hide();
                });
                restoreUserList.appendChild(btn);
            });
        }

        function render() {
            ensureCurrentUser();
            renderUsers();
            renderHeader();
            renderMessages();
            renderRestoreList();
            chatForm.querySelector('button[type="submit"]').disabled = !getCurrentUser();
            chatInput.disabled = !getCurrentUser();
        }

        confirmCloseBtn.addEventListener('click', () => {
            if (state.pendingCloseUserId === null) {
                return;
            }

            const user = state.users.find((u) => u.id === state.pendingCloseUserId);
            if (user) {
                user.isVisible = false;
            }

            if (state.currentUserId === state.pendingCloseUserId) {
                const nextUser = getVisibleUsers()[0];
                state.currentUserId = nextUser ? nextUser.id : null;
            }

            state.pendingCloseUserId = null;
            closeChatModal.hide();
            render();
        });

        closeModalElement.addEventListener('hidden.bs.modal', () => {
            state.pendingCloseUserId = null;
        });

        restoreModalElement.addEventListener('show.bs.modal', renderRestoreList);

        chatForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const text = chatInput.value.trim();

            if (!text) {
                return;
            }

            const user = getCurrentUser();
            if (!user) {
                return;
            }

            user.messages.push({ sender: 'me', name: 'A1', text, time: formatCurrentTime() });
            chatInput.value = '';
            render();
        });

        render();
    });
</script>
@endsection