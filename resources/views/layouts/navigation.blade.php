<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    @php
        // 判斷使用者是否為審核通過的代購人
        $canEnterAgentLobby = Auth::user()?->isApprovedAgent();
        
        // 【核心邏輯強化】：全面偵測代購環境
        // 只要網址包含 'agent'，或是路由名稱以 'agent.' 開頭，就判定為代購模式
        $isAgentMode = request()->is('agent', 'agent/*', '*/agent/*') || request()->routeIs('agent.*');
    @endphp
    <script>
    function submitAndRedirect() {
        const form = document.getElementById('selectForm');
        
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json' // 強制要求 JSON 回應
            }
        })
        .then(response => {
            if (!response.ok) {
                // 如果後端回傳 500 等錯誤，這裡會被捕捉
                throw new Error('後端處理失敗');
            }
            return response.json();
        })
        .then(data => {
            // 成功後執行跳轉
            window.location.href = "{{ route('agent.dashboard') }}";
        })
        .catch(error => {
            console.error('Error:', error);
            alert('處理時發生錯誤，請檢查後端邏輯。建議開啟 F12 查看 Network 分頁以取得詳細錯誤。');
        });
    }
    function clearSelections() {
    fetch("{{ route('agent.notifications.clear') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // 取消畫面上的勾選
        document.querySelectorAll(
            'input[name="selected_notifications[]"]'
        ).forEach(cb => cb.checked = false);

        alert('已清除所有勾選');
    })
    .catch(error => {
        console.error(error);
        alert('清除失敗');
    });
}
</script>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <i class="bi bi-globe-americas text-2xl {{ $isAgentMode ? 'text-indigo-600' : 'text-green-500' }}"></i>
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">
                    @if($isAgentMode)
                        @if($canEnterAgentLobby)
                            <x-nav-link :href="route('agent.dashboard')" :active="request()->routeIs('agent.dashboard')">
                                {{ __('接單大廳') }}
                            </x-nav-link>
                        @else
                            <button
                                type="button"
                                class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out"
                                onclick="openAgentLobbyModal()"
                            >
                                {{ __('接單大廳') }}
                            </button>
                        @endif

                        <x-nav-link :href="route('home')">
                            <i class="bi bi-arrow-left-circle me-1"></i> {{ __('返回買家模式') }}
                        </x-nav-link>

                        @if(request()->is('agent/member*'))

                    <div class="relative inline-block ms-4" x-data="{ openNotification: false }">
                        
                        <button
                            type="button"
                            class="relative inline-flex items-center p-2 text-gray-500 hover:text-gray-700 focus:outline-none"
                            @click="openNotification = !openNotification"
                        >
                            <i class="bi bi-bell-fill text-xl text-indigo-600" style="font-size: 22px;"></i>
                            
                            @if(isset($agentNotifications) && $agentNotifications->count() > 0)
                                <span class="absolute top-1.5 right-1.5 bg-red-600 w-2 h-2 rounded-sm"></span>
                            @endif
                        </button>

                        <div 
                            x-show="openNotification" 
                            @click.away="openNotification = false"
                            class="absolute top-full left-1/2 transform -translate-x-1/2 mt-3 w-64 bg-white border border-gray-300 rounded shadow-md p-2 z-50"
                            style="display: none;"
                        >
                            <div class="fw-bold text-dark border-bottom pb-1 mb-2 px-1 text-start" style="font-size: 14px;">推薦</div>
                            
                            <form id="selectForm" action="{{ route('agent.notifications.select') }}" method="POST">
                                @csrf
                                <div class="max-h-48 overflow-y-auto px-1">
                                    @forelse($agentNotifications as $notify)    
                                        <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                            <div class="d-flex align-items-center">
                                                <input type="checkbox" name="selected_notifications[]" value="{{ $notify->id }}" 
                                                    class="me-3 form-check-input" {{ $notify->is_selected ? 'checked' : '' }}>
                                                
                                                <div class="bg-slate-200 rounded p-1 me-2" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <span class="fw-medium">{{ $notify->buyer->name ?? '未知用戶' }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="py-3 text-center text-muted small">目前沒有推薦的請購人</div>
                                    @endforelse
                                </div>

                                <button type="button" 
                                        onclick="submitAndRedirect()" 
                                        class="w-full mt-3 btn btn-indigo">
                                    前往查看
                                </button>
                                <button type="button"
                                        onclick="clearSelections()"
                                        class="flex-1 btn btn-secondary">
                                    清除勾選
                                </button>
                            </form>

                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-solid border-b-white border-x-transparent border-t-transparent" 
                                style="border-width: 0 8px 8px 8px; margin-bottom: -1px;">
                            </div>
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-solid border-b-gray-300 border-x-transparent border-t-transparent -z-10" 
                                style="border-width: 0 9px 9px 9px;">
                            </div>
                        </div>
                        
                    </div>

                @endif

                    @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('會員專區') }}
                        </x-nav-link>

                        <x-nav-link :href="route('home')">
                            {{ __('返回首頁') }}
                        </x-nav-link>

                        <x-nav-link :href="url('/shopping-cart')" :active="request()->is('shopping-cart')">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-cart3 text-lg"></i>
                                <span>{{ __('結帳') }}</span>
                            </div>
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div class="flex items-center gap-2">
                                @auth
                                    {{ auth()->user()->nickname ?? auth()->user()->name }}
                                @else
                                    {{ __('訪客') }}
                                @endauth
                            </div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">                       
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('個人資料') }}
                        </x-dropdown-link>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <span class="text-red-600">{{ __('登出') }}</span>
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if($isAgentMode)
                <x-responsive-nav-link :href="route('agent.dashboard')" :active="request()->routeIs('agent.dashboard')">
                    {{ __('接單大廳') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('home') }}">
                    {{ __('返回買家模式') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('會員專區') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('home') }}">
                    {{ __('返回首頁') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ auth()->user()?->nickname ?? auth()->user()?->name }}</div>
                <div class="text-xs text-gray-500">{{ auth()->user()?->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('個人資料') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('登出') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>

    @if($isAgentMode && ! $canEnterAgentLobby)
        <div id="agent-lobby-block-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="w-full max-w-md rounded-xl bg-white shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900">{{ __('暫時無法進入接單大廳') }}</h3>
                <p class="mt-3 text-sm text-gray-600 leading-relaxed">{{ __('您目前尚未申請代購人，或審核尚未通過，因此無法進入代購人接單大廳。') }}</p>
                <div class="mt-6 flex justify-end">
                    <button type="button" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700" onclick="closeAgentLobbyModal()">{{ __('我知道了') }}</button>
                </div>
            </div>
        </div>

        <script>
            function openAgentLobbyModal() {
                const modal = document.getElementById('agent-lobby-block-modal');
                if (modal) modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeAgentLobbyModal() {
                const modal = document.getElementById('agent-lobby-block-modal');
                if (modal) modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        </script>
    @endif

</nav>
