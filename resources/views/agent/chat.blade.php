@extends('layouts.front')

@section('content')
<section class="py-5" style="background: linear-gradient(135deg, #f0f3ff 0%, #f8f9fa 100%); min-height: calc(100vh - 80px);">
    <div class="container">
        <div class="mb-4">
            <h1 class="fw-bold mb-2" style="color:#2c3e50;">聊天訊息</h1>
            <p class="text-muted mb-0">處理來自請購人的諮詢，即時回覆報價與狀況。</p>
        </div>

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0" style="color:#4a6cf7;"><i class="bi bi-headset me-2"></i>GlobalBuy chat</h5>
                <a href="{{ route('agent.member') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> 返回會員專區
                </a>
            </div>

            <div class="card-body p-0" id="chat-app">
                <div class="row g-0" style="min-height: 620px;">
                    <aside class="col-12 col-lg-4 col-xl-3 border-end bg-white p-3">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3 px-1">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-people-fill text-primary"></i>
                                <span class="fw-semibold">聊天對象</span>
                            </div>
                            <button id="restore-chat-btn" type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#restoreChatModal">
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
                            <button type="submit" class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2 text-nowrap" style="background-color: #4a6cf7; border:none;">
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

<div class="modal fade" id="closeChatModal" tabindex="-1" aria-labelledby="closeChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="closeChatModalLabel">提示</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                是否關閉與此請購人的聊天？
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">否</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" id="confirm-close-chat">是</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="restoreChatModal" tabindex="-1" aria-labelledby="restoreChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="restoreChatModalLabel">新增/恢復請購人</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div id="restore-user-list" class="d-flex flex-column gap-2"></div>
                <p id="restore-empty-tip" class="text-muted mb-0 d-none">目前沒有可新增的請購人。</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const state = {
            currentUserId: 101,
            pendingCloseUserId: null,
            users: [
                {
                    id: 101,
                    name: '請購人 A1',
                    intro: '詢問：大正百龍館、奇巧巧克力',
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
                    intro: '詢問：日本藥妝代購',
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
        const confirmCloseBtn = document.getElementById('confirm-close-chat');
        const restoreUserList = document.getElementById('restore-user-list');
        const restoreEmptyTip = document.getElementById('restore-empty-tip');

        const closeChatModal = new bootstrap.Modal(document.getElementById('closeChatModal'));
        const restoreChatModal = new bootstrap.Modal(document.getElementById('restoreChatModal'));

        function getVisibleUsers() { return state.users.filter((u) => u.isVisible); }
        function getHiddenUsers() { return state.users.filter((u) => !u.isVisible); }
        function getCurrentUser() { return state.users.find((u) => u.id === state.currentUserId) || null; }

        function ensureCurrentUser() {
            const current = getCurrentUser();
            if (!current || !current.isVisible) {
                const firstVisible = getVisibleUsers()[0];
                state.currentUserId = firstVisible ? firstVisible.id : null;
            }
        }

        // 渲染清單 (同步右圖帶有 X 的樣式)
        function renderUsers() {
            userList.innerHTML = '';
            const visibleUsers = getVisibleUsers();
            if (visibleUsers.length === 0) {
                userList.innerHTML = '<div class="text-muted small px-2 py-4 text-center">目前沒有開啟中的聊天。</div>';
                return;
            }

            visibleUsers.forEach((user) => {
                const isActive = user.id === state.currentUserId;
                const button = document.createElement('button');
                button.className = `btn text-start border rounded-4 p-3 mb-1 w-100 transition ${isActive ? 'border-primary-subtle bg-primary-subtle' : 'bg-white shadow-sm hover-shadow-sm'}`;

                button.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="d-flex gap-2 align-items-center min-w-0">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold border" 
                                 style="width:42px;height:42px; flex-shrink:0; ${isActive ? 'background:#4a6cf7;color:#fff;' : 'background:#eee;color:#666;'}">
                                ${user.name.slice(-1)}
                            </div>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark text-truncate">${user.name}</div>
                                <div class="text-muted small text-truncate" style="max-width: 120px;">${user.messages[user.messages.length - 1]?.text ?? ''}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            ${user.unread > 0 ? `<span class="badge rounded-pill bg-danger">${user.unread}</span>` : ''}
                            <span class="text-muted fw-bold" role="button" data-close-user-id="${user.id}" style="font-size:18px;">×</span>
                        </div>
                    </div>
                `;

                button.onclick = (e) => {
                    if (e.target.closest('[data-close-user-id]')) return;
                    state.currentUserId = user.id;
                    user.unread = 0;
                    render();
                };
                userList.appendChild(button);
            });

            userList.querySelectorAll('[data-close-user-id]').forEach(btn => {
                btn.onclick = (e) => {
                    e.stopPropagation();
                    state.pendingCloseUserId = Number(btn.dataset.closeUserId);
                    closeChatModal.show();
                };
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
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" 
                         style="width:46px;height:46px;background:#4a6cf7;">${user.name.slice(-1)}</div>
                    <div>
                        <div class="fw-bold" style="color:#2c3e50;">${user.name}</div>
                        <div class="text-muted small"><span class="badge bg-light text-primary border border-primary-subtle">請購中</span> ${user.intro}</div>
                    </div>
                </div>
            `;
        }

        // 渲染訊息泡泡 (同步右圖樣式：寬度限制、泡泡樣式)
        function renderMessages() {
            const user = getCurrentUser();
            chatMessages.innerHTML = '';
            if (!user) {
                chatMessages.innerHTML = '<div class="text-muted text-center py-5">請點選左側名單開始對話</div>';
                return;
            }

            user.messages.forEach((msg) => {
                const isMe = msg.sender === 'me';
                const row = document.createElement('div');
                row.className = `d-flex mb-3 ${isMe ? 'justify-content-end' : 'justify-content-start'}`;

                const bubbleWrap = document.createElement('div');
                bubbleWrap.className = `d-flex flex-column ${isMe ? 'align-items-end' : 'align-items-start'}`;

                const topRow = document.createElement('div');
                topRow.className = `d-flex align-items-end gap-2 ${isMe ? 'flex-row-reverse' : ''}`;

                const avatar = document.createElement('div');
                avatar.className = 'rounded-circle border d-flex align-items-center justify-content-center fw-bold text-secondary bg-white';
                avatar.style.cssText = 'width:34px;height:34px;font-size:11px;flex-shrink:0;';
                avatar.textContent = isMe ? 'Agent' : msg.name;

                const bubble = document.createElement('div');
                bubble.className = 'px-3 py-2 rounded-3 border fw-semibold shadow-sm';
                bubble.style.cssText = `
                    display: inline-block; width: fit-content; max-width: 18ch;
                    white-space: normal; overflow-wrap: anywhere; word-break: break-all; line-height: 1.5;
                    background-color: ${isMe ? '#eef2ff' : '#ffffff'};
                    border-color: ${isMe ? '#dbe4ff' : '#dee2e6'};
                    color: #2c3e50;
                `;
                bubble.textContent = msg.text;

                topRow.appendChild(avatar);
                topRow.appendChild(bubble);
                bubbleWrap.appendChild(topRow);

                const timeNode = document.createElement('div');
                timeNode.className = `small text-muted mt-1 px-1 ${isMe ? 'text-end' : 'text-start'}`;
                timeNode.textContent = msg.time;
                bubbleWrap.appendChild(timeNode);

                row.appendChild(bubbleWrap);
                chatMessages.appendChild(row);
            });
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // 渲染新增聊天清單 (同步右圖)
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
                btn.className = 'btn btn-outline-primary text-start rounded-3 d-flex justify-content-between align-items-center w-100 mb-2';
                btn.innerHTML = `<span>${user.name}</span><span class="small opacity-75">開啟對話</span>`;
                btn.onclick = () => {
                    user.isVisible = true;
                    state.currentUserId = user.id;
                    render();
                    restoreChatModal.hide();
                };
                restoreUserList.appendChild(btn);
            });
        }

        function formatTime() {
            const now = new Date();
            return `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
        }

        chatForm.onsubmit = (e) => {
            e.preventDefault();
            const text = chatInput.value.trim();
            if (!text || !getCurrentUser()) return;
            getCurrentUser().messages.push({ sender: 'me', name: 'Agent', text, time: formatTime() });
            chatInput.value = '';
            render();
        };

        confirmCloseBtn.onclick = () => {
            const user = state.users.find(u => u.id === state.pendingCloseUserId);
            if (user) user.isVisible = false;
            ensureCurrentUser();
            closeChatModal.hide();
            render();
        };

        function render() {
            ensureCurrentUser();
            renderUsers();
            renderHeader();
            renderMessages();
            renderRestoreList();
            chatInput.disabled = !getCurrentUser();
        }

        render();
    });
</script>
@endsection