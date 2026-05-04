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

        /* 角色選擇按鈕 - 增強版樣式 */
        .btn-role-option {
            border-radius: 16px;
            padding: 20px !important;
            font-weight: 700 !important;
            font-size: 1.1rem !important;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-width: 2px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-role-primary {
            background-color: #56ab91;
            border-color: #56ab91;
            color: white;
            box-shadow: 0 8px 20px rgba(86, 171, 145, 0.2);
        }

        .btn-role-primary:hover {
            background-color: #458e78;
            border-color: #458e78;
            color: white;
            transform: scale(1.03);
            box-shadow: 0 12px 25px rgba(86, 171, 145, 0.3);
        }

        .btn-role-secondary {
            background-color: #ffffff;
            border-color: #e0e0e0;
            color: #666;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .btn-role-secondary:hover {
            background-color: #f8f9fa;
            border-color: #56ab91;
            color: #56ab91;
            transform: scale(1.03);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        footer {
            background-color: #2C3E50;
            color: #ecf0f1;
            padding: 50px 0;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
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
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-link text-danger text-decoration-none">登出</button>
                        </form>
                    @else
                        <!-- 未登入：顯示登入與註冊 -->
                        <a href="{{ route('login') }}" class="btn btn-outline-dark rounded-pill px-4">登入</a>
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
    


    <!-- 身分選擇彈跳視窗 (僅在登入後且符合身分時觸發) -->
    @if(session('show_role_selector'))
    <div class="modal fade" id="roleSelectorModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-2xl rounded-5 overflow-hidden">
                <div class="modal-body text-center p-5">
                    <div class="role-modal-icon">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <h3 class="fw-black mb-3">歡迎回來！</h3>
                    <p class="text-muted mb-4 px-3">系統偵測到您具備代購人身分。<br>今天想要先處理哪部分的工作呢？</p>
                    
                    <div class="d-grid gap-3">
                        <!-- 選項 1：進入代購大廳 -->
                        <a href="{{ route('agent.dashboard') }}" class="btn btn-role-option btn-role-primary">
                            <i class="bi bi-shop"></i>
                            進入代購接單大廳
                        </a>
                        
                        <!-- 選項 2：留在目前頁面  -->
                        <button type="button" class="btn btn-role-option btn-role-secondary" data-bs-dismiss="modal">
                            留在首頁
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    
    @if(session('show_role_selector'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 實例化並顯示 Modal
            const roleModalElement = document.getElementById('roleSelectorModal');
            if (roleModalElement) {
                const roleModal = new bootstrap.Modal(roleModalElement);
                roleModal.show();
            }
        });
    </script>
    @endif

    @stack('scripts')
</body>
</html>