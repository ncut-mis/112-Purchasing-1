<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    @php
        $canEnterAgentLobby = Auth::user()?->isApprovedAgent();
    @endphp

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <i class="bi bi-globe-americas text-2xl text-green-600"></i>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <!-- 判斷目前是否在代購人相關路由 (以 agent. 開頭) -->
                    @if(request()->routeIs('agent.*'))
                        <!-- 代購人看到的文字 -->
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
                            {{ __('返回買家模式') }}
                        </x-nav-link>
                    @else
                        <!-- 一般請購人 (買家) 看到的文字保持不變 -->
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('會員專區') }}
                        </x-nav-link>
                        <x-nav-link :href="route('home')">
                            {{ __('返回首頁') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

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
                        
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                   onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('登出') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
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

    <!-- Responsive Navigation Menu (手機版同步判斷) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if(request()->routeIs('agent.*'))
                @if($canEnterAgentLobby)
                    <x-responsive-nav-link :href="route('agent.dashboard')" :active="request()->routeIs('agent.dashboard')">
                        {{ __('接單大廳') }}
                    </x-responsive-nav-link>
                @else
                    <button
                        type="button"
                        class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out"
                        onclick="openAgentLobbyModal()"
                    >
                        {{ __('接單大廳') }}
                    </button>
                @endif
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

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('個人資料') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
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

    @if(request()->routeIs('agent.*') && ! $canEnterAgentLobby)
        <div id="agent-lobby-block-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="w-full max-w-md rounded-xl bg-white shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-900">暫時無法進入接單大廳</h3>
                <p class="mt-3 text-sm text-gray-600 leading-relaxed">您目前尚未申請代購人，或審核尚未通過，因此無法進入代購人接單大廳。</p>
                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700"
                        onclick="closeAgentLobbyModal()"
                    >
                        我知道了
                    </button>
                </div>
            </div>
        </div>

        <script>
            function openAgentLobbyModal() {
                const modal = document.getElementById('agent-lobby-block-modal');
                if (!modal) return;

                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeAgentLobbyModal() {
                const modal = document.getElementById('agent-lobby-block-modal');
                if (!modal) return;

                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        </script>
    @endif
</nav>