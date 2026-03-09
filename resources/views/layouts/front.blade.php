<!doctype html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel代購') }} - 全球好物輕鬆買</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #5A9E8E; 
            --secondary-color: #F3F7F5;
            --text-color: #333333;
        }

        body {
            font-family: 'Noto Sans TC', sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-color);
        }

        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 15px 0;
        }
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }
        .nav-link {
            color: #555;
            font-weight: 500;
            margin: 0 10px;
        }
        .nav-link:hover { color: var(--primary-color); }

        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            border-radius: 50px;
            padding: 8px 25px;
        }
        .btn-primary-custom:hover {
            background-color: #488275;
            color: white;
        }

        .user-btn-custom {
            border: 2px solid #28a745 !important;
            background: linear-gradient(135deg, #f8fff9 0%, white 100%) !important;
            color: #28a745 !important;
            transition: all 0.3s ease;
        }

        .user-btn-custom:hover {
             background: #28a745 !important;
            transform: scale(1.05);
        }

        .user-btn-custom img {
             filter: brightness(0) saturate(100%) invert(25%) sepia(100%) saturate(1000%) hue-rotate(120deg);
        }



        footer {
            background-color: #2C3E50;
            color: #ecf0f1;
            padding: 50px 0;
        }
    </style>
</head>
<body>

    <!-- Navigation (從第 78 行開始替換) -->
    <nav class="navbar navbar-expand-lg fixed-top bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <i class="bi bi-globe-americas me-2 text-success"></i>
                <span class="fw-bold">GlobalBuy</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">首頁</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('store') }}">尋找代購人</a>
                    </li>

                    <!-- 新增：會員專區 (僅登入後顯示於主選單) -->
                    @auth
                    <li class="nav-item">
                        <a class="nav-link fw-bold text-success" href="{{ route('dashboard') }}">
                            <i class="bi bi-person-badge me-1"></i>會員專區
                        </a>
                    </li>
                    @endauth
                </ul>
                
                <div class="d-flex align-items-center gap-2">
                    @auth
                        <!-- 已登入：顯示進入控制台與登出 -->
                        <a href="{{ url('/dashboard') }}" class="btn btn-success rounded-pill px-4">
                            <img src="{{ auth()->user()->avatar ?? asset('images/user.svg') }}" 
                                alt="用戶頭像" width="24" height="24" class="rounded-circle" 
                                style="filter: brightness(0) invert(1);">
                        </a>


                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-link text-danger text-decoration-none">登出</button>
                        </form>
                    @else
                        <!-- 未登入：顯示登入與註冊 -->
                        <a href="#" class="btn btn-outline-dark rounded-pill px-4 js-login-choice" data-user-login="{{ route('login') }}" data-admin-login="{{ route('admin.login') }}">登入</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-success rounded-pill px-4 text-white" style="background-color: #56ab91;">註冊</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <main style="margin-top: 80px;">
        @yield('content')
    </main>



    @guest
    <div class="modal fade" id="loginChoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">請選擇登入身分</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted">請選擇要使用「一般使用者」或「管理員」登入。</p>
                    <div class="d-grid gap-2">
                        <a id="userLoginBtn" href="{{ route('login') }}" class="btn btn-success rounded-pill">一般使用者登入</a>
                        <a id="adminLoginBtn" href="{{ route('admin.login') }}" class="btn btn-outline-dark rounded-pill">管理員登入</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endguest

    <!-- Footer -->
    <footer class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="fw-bold mb-3">GlobalBuy</h5>
                    <p class="text-white-50">連結全球好物，讓購物沒有國界。</p>
                </div>
                <div class="col-md-8 text-md-end">
                    <p class="text-white-50">&copy; {{ date('Y') }} GlobalBuy Platform.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @guest
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loginChoiceModalEl = document.getElementById('loginChoiceModal');
            if (!loginChoiceModalEl) return;

            const loginChoiceModal = new bootstrap.Modal(loginChoiceModalEl);
            const userLoginBtn = document.getElementById('userLoginBtn');
            const adminLoginBtn = document.getElementById('adminLoginBtn');

            document.querySelectorAll('.js-login-choice').forEach((trigger) => {
                trigger.addEventListener('click', function (event) {
                    event.preventDefault();

                    const userLoginUrl = this.dataset.userLogin || '{{ route('login') }}';
                    const adminLoginUrl = this.dataset.adminLogin || '{{ route('admin.login') }}';

                    userLoginBtn.setAttribute('href', userLoginUrl);
                    adminLoginBtn.setAttribute('href', adminLoginUrl);

                    loginChoiceModal.show();
                });
            });
        });
    </script>
    @endguest

</body>
</html>