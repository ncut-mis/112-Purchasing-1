<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('會員專區') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

           
            <!-- 頂部統計概覽 -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                    <div class="text-sm text-gray-500 mb-1">進行中的請購</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['ongoing_requests'] }}</div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500">
                    <div class="text-sm text-gray-500 mb-1">進行中的跟單</div>
                    <div class="text-2xl font-bold text-gray-800">0</div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                    <div class="text-sm text-gray-500 mb-1">未讀訊息</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $stats['unread_messages'] }}</div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-pink-500">
                    <div class="text-sm text-gray-500 mb-1">收藏貼文</div>
                     <div id="favorite-posts-count" class="text-2xl font-bold text-gray-800" data-count="{{ $stats['favorite_posts'] }}">{{ $stats['favorite_posts'] }}</div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-6">

               

                <!-- 左側功能選單 -->
                <div class="w-full md:w-64 space-y-2">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-4 border-b bg-gray-50 font-bold text-gray-700">功能列表</div>

                        @php
                            // 取得當前使用者的代購申請紀錄
                            $app = Auth::user()->agentApplication;
                        @endphp

                        <nav class="p-2 space-y-1">
                            <a href="{{ route('dashboard', ['section' => 'request-lists']) }}" class="flex items-center space-x-3 p-3 rounded-lg {{ $currentSection === 'request-lists' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-600 hover:bg-gray-50 transition' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                <span>請購清單</span>
                            </a>

                            <a href="{{ route('dashboard', ['section' => 'favorite-posts']) }}" class="flex items-center space-x-3 p-3 rounded-lg {{ $currentSection === 'favorite-posts' ? 'bg-pink-50 text-pink-600 font-medium' : 'text-gray-600 hover:bg-gray-50 transition' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                                <span>收藏貼文</span>
                            </a>
                            
                            <a href="{{ route('dashboard', ['section' => 'notifications']) }}" class="flex items-center space-x-3 p-3 rounded-lg {{ $currentSection === 'notifications' ? 'bg-amber-50 text-amber-700 font-medium' : 'text-gray-600 hover:bg-gray-50 transition' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                <span>通知中心</span>
                            </a>

                            <a href="{{ route('dashboard', ['section' => 'follow-orders']) }}" class="flex items-center space-x-3 p-3 rounded-lg {{ $currentSection === 'follow-orders' ? 'bg-purple-50 text-purple-600 font-medium' : 'text-gray-600 hover:bg-gray-50 transition' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                <span>跟單紀錄</span>
                            </a>

                            <!-- 聊天 (新移動位置) -->

                             <a href="{{ route('messages.index') }}" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                </svg>

                                <span>聊天訊息</span>
                            </a>


                            <!-- 歷史紀錄 (新移動位置) -->
                            <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>歷史紀錄</span>
                            </a>

                            <!-- 評價 (新移動位置) -->
                            <a href="#" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                <span>評價中心</span>
                            </a>

                            <!-- 動態身分判斷區域 -->
                            <div class="border-t mt-2 pt-2">
                                @if($app && $app->status == 'approved')
                                    <!-- 1. 審核通過：顯示進入代購大廳入口 -->
                                    <a href="{{ route('agent.dashboard') }}" class="flex items-center space-x-3 p-3 rounded-lg text-indigo-700 bg-indigo-50 font-bold transition group">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                        <span class="group-hover:underline">代購人頁面</span>
                                    </a>

                                @elseif($app && $app->status == 'pending')
                                    <!-- 2. 審核中：顯示狀態提示，導向進度頁面 -->
                                    <a href="{{ route('agent.status') }}" class="flex items-center space-x-3 p-3 rounded-lg text-amber-600 bg-amber-50 transition group">
                                        <svg class="w-5 h-5 text-amber-500 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="font-medium group-hover:underline">申請審核中...</span>
                                    </a>
                                @else
                                    <!-- 3. 未申請或被拒絕：顯示申請按鈕 -->
                                    <a href="{{ route('agent.apply') }}" class="flex items-center space-x-3 p-3 rounded-lg text-gray-600 hover:bg-gray-50 transition group">
                                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="font-medium group-hover:underline">申請代購人</span>
                                    </a>

                                @endif
                            </div>
                        </nav>
                    </div>
                </div>



                <!-- 右側主內容區 -->
                <div class="flex-1 space-y-6">

                    @if($currentSection === 'favorite-posts')
                        @includeIf('dashboard/partials/favorite-posts/index')
                    @elseif($currentSection === 'notifications')
                        @includeIf('dashboard/partials/notifications/index')
                    @elseif($currentSection === 'follow-orders')
                        @includeIf('dashboard/partials/follow-orders/index')
                    @elseif($currentSection === 'messages')
                        @includeIf('dashboard/partials/messages/index')
                    @else
                        @includeIf('dashboard/partials/request-lists/index')
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        const dashboardFavoriteToggleUrl = @json(route('favorite.toggle'));
        const dashboardCsrfToken = @json(csrf_token());

        function openRequestDetailModal(id) {
            const modal = document.getElementById(`request-detail-modal-${id}`);

            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeRequestDetailModal(id) {
            const modal = document.getElementById(`request-detail-modal-${id}`);

            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        function openFollowOrderModal(id) {
            const modal = document.getElementById(`follow-order-modal-${id}`);

            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeFollowOrderModal(id) {
            const modal = document.getElementById(`follow-order-modal-${id}`);

            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

         function openRequestCountdownModal(id) {
            const modal = document.getElementById(`request-countdown-modal-${id}`);
            if (!modal) {
                return;
            }

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            startRequestCountdown(id);
        }

        function closeRequestCountdownModal(id) {
            const modal = document.getElementById(`request-countdown-modal-${id}`);
            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            if (window[`requestCountdownTimer_${id}`]) {
                clearInterval(window[`requestCountdownTimer_${id}`]);
                window[`requestCountdownTimer_${id}`] = null;
            }
            document.body.classList.remove('overflow-hidden');
        }

        function startRequestCountdown(id) {
            const target = document.getElementById(`request-countdown-text-${id}`);
            if (!target) {
                return;
            }

            const endAtRaw = target.dataset.endAt;
            if (!endAtRaw) {
                target.textContent = '未提供截止時間';
                return;
            }

            const endAt = new Date(endAtRaw.replace(' ', 'T'));
            if (Number.isNaN(endAt.getTime())) {
                target.textContent = '時間格式錯誤';
                return;
            }

            const tick = () => {
                const now = new Date();
                const diffMs = endAt.getTime() - now.getTime();

                if (diffMs <= 0) {
                    target.textContent = '已截止';
                    return;
                }

                const totalSeconds = Math.floor(diffMs / 1000);
                const days = Math.floor(totalSeconds / 86400);
                const hours = Math.floor((totalSeconds % 86400) / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                target.textContent = `${days}天 ${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            };

            tick();
            if (window[`requestCountdownTimer_${id}`]) {
                clearInterval(window[`requestCountdownTimer_${id}`]);
            }
            window[`requestCountdownTimer_${id}`] = setInterval(tick, 1000);
        }

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            if (favoriteUnfavoriteModal && !favoriteUnfavoriteModal.classList.contains('hidden')) {
                closeFavoriteUnfavoriteModal();
                return;
            }

            document.querySelectorAll('.request-detail-modal').forEach((modal) => {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            });

            document.querySelectorAll('.request-countdown-modal').forEach((modal) => {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            });

        });

        document.querySelectorAll('.follow-order-modal').forEach((modal) => {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            });

        function handleRequestDetailBackdrop(event, id) {
            if (event.target.id === `request-detail-modal-${id}`) {
                closeRequestDetailModal(id);
            }
        }
        function handleFollowOrderBackdrop(event, id) {
            if (event.target.id === `follow-order-modal-${id}`) {
                closeFollowOrderModal(id);
            }
        }

        function handleRequestCountdownBackdrop(event, id) {
            if (event.target.id === `request-countdown-modal-${id}`) {
                closeRequestCountdownModal(id);
            }
        }

        const favoriteUnfavoriteModal = document.getElementById('favorite-unfavorite-modal');
        const favoriteUnfavoriteCancelButton = document.getElementById('favorite-unfavorite-cancel');
        const favoriteUnfavoriteConfirmButton = document.getElementById('favorite-unfavorite-confirm');
        const favoritePostsCountElement = document.getElementById('favorite-posts-count');
        let pendingFavoriteRemovalButton = null;
        const favoriteEmptyStateHtml = '<div class="rounded-2xl border border-dashed border-pink-200 bg-pink-50/40 px-6 py-12 text-center text-sm text-gray-500">目前尚未收藏任何代購貼文，請先到首頁的「最新代購連線」按下愛心收藏。</div>';

        function updateFavoritePostsCount(nextCount) {
            if (!favoritePostsCountElement) {
                return;
            }

            const safeCount = Math.max(0, Number.parseInt(nextCount, 10) || 0);
            favoritePostsCountElement.dataset.count = String(safeCount);
            favoritePostsCountElement.textContent = String(safeCount);
        }

        function decrementFavoritePostsCount() {
            if (!favoritePostsCountElement) {
                return;
            }

            updateFavoritePostsCount((favoritePostsCountElement.dataset.count || favoritePostsCountElement.textContent) - 1);
        }


        function closeFavoriteUnfavoriteModal() {
            if (!favoriteUnfavoriteModal) {
                return;
            }

            favoriteUnfavoriteModal.classList.add('hidden');
            pendingFavoriteRemovalButton = null;
            document.body.classList.remove('overflow-hidden');

            if (favoriteUnfavoriteConfirmButton) {
                favoriteUnfavoriteConfirmButton.disabled = false;
                favoriteUnfavoriteConfirmButton.textContent = '確定移除';
            }
        }

        function openFavoriteUnfavoriteModal(button) {
            if (!favoriteUnfavoriteModal || !button) {
                return;
            }

            pendingFavoriteRemovalButton = button;
            favoriteUnfavoriteModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        async function removeFavoriteFromDashboard(button) {
            const agentPostId = button?.dataset.agentPostId;
            if (!agentPostId || button.disabled) {
                return;
            }

            button.disabled = true;
            if (favoriteUnfavoriteConfirmButton) {
                favoriteUnfavoriteConfirmButton.disabled = true;
                favoriteUnfavoriteConfirmButton.textContent = '移除中...';
            }

            try {
                const response = await fetch(dashboardFavoriteToggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': dashboardCsrfToken,
                    },
                    body: JSON.stringify({
                        type: 'agent_post',
                        id: agentPostId,
                    }),
                });

                if (!response.ok) {
                    throw new Error('favorite toggle failed');
                }

                const data = await response.json();
                if (data.status === 'removed') {
                    const card = button.closest('.favorite-post-item');
                    card?.remove();
                    decrementFavoritePostsCount();

                    const list = document.getElementById('favorite-post-list');
                    if (list && !list.querySelector('.favorite-post-item')) {
                        list.innerHTML = favoriteEmptyStateHtml;
                    }
                }

                closeFavoriteUnfavoriteModal();
            } catch (error) {
                console.error(error);
                alert('更新收藏狀態失敗，請稍後再試。');

                if (favoriteUnfavoriteConfirmButton) {
                    favoriteUnfavoriteConfirmButton.disabled = false;
                    favoriteUnfavoriteConfirmButton.textContent = '確定移除';
                }

                button.disabled = false;
            }
        }

        document.querySelectorAll('.dashboard-favorite-toggle').forEach((button) => {
            button.addEventListener('click', () => {
                const agentPostId = button.dataset.agentPostId;
                if (!agentPostId || button.disabled) {
                    return;
                }

                openFavoriteUnfavoriteModal(button);
            });
        });

        favoriteUnfavoriteCancelButton?.addEventListener('click', closeFavoriteUnfavoriteModal);
        favoriteUnfavoriteModal?.addEventListener('click', (event) => {
            if (event.target === favoriteUnfavoriteModal) {
                closeFavoriteUnfavoriteModal();
            }
        });
        favoriteUnfavoriteConfirmButton?.addEventListener('click', () => {
            if (pendingFavoriteRemovalButton) {
                removeFavoriteFromDashboard(pendingFavoriteRemovalButton);
            }
        });


        function openEditModal(id) {
            const modal = document.getElementById(`edit-modal-${id}`);

            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

            }

        }



        function getEditItemsWrapperByListId(id) {
            return document.querySelector(`.edit-items-wrapper[data-request-list-id="${id}"]`);
        }

        function getVisibleEditCards(wrapper) {
            return wrapper ? wrapper.querySelectorAll('.edit-item-card:not(.hidden)') : [];
        }

        function updateEditItemUi(wrapper) {
            if (!wrapper) return;

            const maxItems = parseInt(wrapper.dataset.maxItems || '3', 10);
            const visibleCards = Array.from(getVisibleEditCards(wrapper));
            const addBtn = wrapper.querySelector('.edit-add-item-btn');
            const hint = wrapper.querySelector('.edit-item-limit-hint');

            visibleCards.forEach((card) => {
                const removeBtn = card.querySelector('button[onclick="removeEditItem(this)"]');
                if (removeBtn) {
                    removeBtn.disabled = visibleCards.length <= 1;
                    removeBtn.classList.toggle('opacity-50', visibleCards.length <= 1);
                    removeBtn.classList.toggle('cursor-not-allowed', visibleCards.length <= 1);
                }
            });

            const remaining = maxItems - visibleCards.length;
            if (addBtn) {
                addBtn.disabled = remaining <= 0;
            }

            if (hint) {
                hint.textContent = remaining > 0
                    ? `還可再新增 ${remaining} 項商品。`
                    : '已達商品上限（最多 3 項）。';
            }
        }

        function addEditItem(id) {
            const wrapper = getEditItemsWrapperByListId(id);
            if (!wrapper) return;

            const maxItems = parseInt(wrapper.dataset.maxItems || '3', 10);
            const visibleCards = getVisibleEditCards(wrapper);
            if (visibleCards.length >= maxItems) {
                return;
            }

            const template = document.getElementById(`edit-item-template-${id}`);
            const list = wrapper.querySelector('.edit-item-list');
            if (!template || !list) return;

            const nextIndex = parseInt(wrapper.dataset.nextIndex || String(visibleCards.length), 10);
            const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex));
            list.insertAdjacentHTML('beforeend', html);
            wrapper.dataset.nextIndex = String(nextIndex + 1);

            updateEditItemUi(wrapper);
        }

        function removeEditItem(button) {
            const card = button.closest('.edit-item-card');
            if (!card) return;

            const wrapper = button.closest('.edit-items-wrapper');
            if (!wrapper) return;

            const visibleCards = getVisibleEditCards(wrapper);
            if (visibleCards.length <= 1) {
                alert('至少需保留一項商品');
                return;
            }

            const isExisting = card.dataset.existing === '1';
            const flag = card.querySelector('.remove-flag');

            if (isExisting && flag) {
                flag.value = '1';
                card.classList.add('hidden');
            } else {
                card.remove();
            }

            updateEditItemUi(wrapper);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.edit-items-wrapper').forEach(updateEditItemUi);
            // 取得所有開頭為 followOrderModal- 的視窗
    const orderModals = document.querySelectorAll('[id^="followOrderModal-"]');

    orderModals.forEach(modal => {
        const qtyInputs = modal.querySelectorAll('.qty-input');
        const totalAmountDisplay = modal.querySelector('.total-amount');

        // 定義計算總額的函式
        const calculateTotal = () => {
            let grandTotal = 0;
            
            // 遍歷視窗內每一列商品
            modal.querySelectorAll('.product-row').forEach(row => {
                const price = parseFloat(row.getAttribute('data-price')) || 0;
                const quantity = parseInt(row.querySelector('.qty-input').value) || 0;
                grandTotal += price * quantity;
            });

            // 更新顯示金額（加上千分位符號）
            totalAmountDisplay.textContent = grandTotal.toLocaleString();
        };

        // 為每個輸入框綁定監聽事件
        qtyInputs.forEach(input => {
            // 'input' 事件：當使用者輸入、點擊上下箭頭、或透過 JS 改變數值時觸發
            input.addEventListener('input', function() {
                // 防呆：確保數值不低於 0
                if (this.value < 0) this.value = 0;
                
                // 防呆：確保數值不超過 max (剩餘數量)
                const max = parseInt(this.getAttribute('max'));
                if (parseInt(this.value) > max) {
                    alert('不能超過可下單數量：' + max);
                    this.value = max;
                }

                calculateTotal();
            });
        });
    });
});

        function closeEditModal(id) {
            const modal = document.getElementById(`edit-modal-${id}`);
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }



// 1. 控制選擇視窗
    function openFollowChoiceModal(id, title, searchUrl) {
        const modal = document.getElementById('choiceModal');
        const goToHomeBtn = document.getElementById('goToHomeBtn');
        const followHereBtn = document.getElementById('followHereBtn');

        goToHomeBtn.href = searchUrl; // 設定跳轉網址
        followHereBtn.onclick = function() {
            closeChoiceModal();
            openOrderModal(id); // 開啟對應的跟單 Modal
        };

        modal.classList.remove('hidden');
    }

    function closeChoiceModal() {
        document.getElementById('choiceModal').classList.add('hidden');
    }

    // 2. 控制跟單視窗
    function openOrderModal(id) {
        document.getElementById(`followOrderModal-${id}`).classList.remove('hidden');
    }

    function closeOrderModal(id) {
        document.getElementById(`followOrderModal-${id}`).classList.add('hidden');
    }

    // 3. 計算金額邏輯 (初始化所有 Modal 的事件)
    document.querySelectorAll('[id^="followOrderModal-"]').forEach(modal => {
        const updateTotals = () => {
            let grandTotal = 0;
            modal.querySelectorAll('.product-row').forEach(row => {
                const price = parseFloat(row.dataset.price) || 0;
                const qty = parseInt(row.querySelector('.qty-input').value) || 0;
                grandTotal += price * qty;
            });
            modal.querySelector('.total-amount').textContent = grandTotal.toLocaleString();
        };

        modal.addEventListener('click', (e) => {
            const input = e.target.closest('.product-row')?.querySelector('.qty-input');
            if (!input) return;

            if (e.target.closest('.qty-plus')) {
                const max = parseInt(input.max);
                if (parseInt(input.value) < max) {
                    input.value = parseInt(input.value) + 1;
                    updateTotals();
                }
            }
            if (e.target.closest('.qty-minus')) {
                if (parseInt(input.value) > 0) {
                    input.value = parseInt(input.value) - 1;
                    updateTotals();
                }
            }
        });

        // 監聽手輸入
        modal.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('change', () => {
                const max = parseInt(input.max);
                if (parseInt(input.value) > max) input.value = max;
                if (parseInt(input.value) < 0) input.value = 0;
                updateTotals();
            });
        });
    });






    </script>


</x-app-layout>