<!doctype html>
<html lang="zh-TW">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Untree.co">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">

    <meta name="description" content="{{ config('app.description', '') }}" />
    <meta name="keywords" content="{{ config('app.keywords', 'bootstrap, bootstrap5, ecommerce') }}" />

    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/tiny-slider.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <title>{{ config('app.name', 'GlobalBuy') }} @yield('title')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
    .btn-primary-custom {
        background-color: #5A9E8E !important;
        border-color: #5A9E8E !important;
        color: white !important;
        border-radius: 50px !important;
        padding: 8px 25px !important;
    }
    .btn-primary-custom:hover {
        background-color: #488275 !important;
        color: white !important;
    }
    </style>
</head>

<body>
    <!-- Start Header/Navigation -->
    <nav class="custom-navbar navbar navbar navbar-expand-md navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">GlobalBuy<span>.</span></a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsFurni">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarsFurni">
                <ul class="custom-navbar-nav navbar-nav ms-auto mb-2 mb-md-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">首頁</a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('store') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('store') }}">找代購</a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#">許願池</a></li>
                </ul>

                <ul class="custom-navbar-cta navbar-nav mb-2 mb-md-0 ms-5">
                    @auth
                        <li>
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <img src="{{ auth()->user()->avatar ?? asset('images/user.svg') }}" 
                                     alt="用戶頭像" width="24" height="24" class="rounded-circle">
                            </a>
                        </li>
                    @else
                        <li><a class="nav-link" href="{{ route('login') }}"><img src="{{ asset('images/user.svg') }}"></a></li>
                    @endauth
                    <li><a class="nav-link" href="#"><img src="{{ asset('images/cart.svg') }}"></a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    @yield('hero')

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer-section">
        <div class="container relative">
            <div class="sofa-img">
                <img src="{{ asset('images/sofa.png') }}" alt="Image" class="img-fluid">
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="subscription-form">
                        <h3 class="d-flex align-items-center">
                            <span class="me-1">
                                <img src="{{ asset('images/envelope-outline.svg') }}" alt="Image" class="img-fluid">
                            </span>
                            <span>訂閱電子報</span>
                        </h3>
                        <form action="#" class="row g-3">
                            <div class="col-auto">
                                <input type="text" class="form-control" placeholder="姓名">
                            </div>
                            <div class="col-auto">
                                <input type="email" class="form-control" placeholder="電子郵件">
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary">
                                    <span class="fa fa-paper-plane"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row g-5 mb-5">
                <div class="col-lg-4">
                    <div class="mb-4 footer-logo-wrap">
                        <a href="{{ route('home') }}" class="footer-logo">GlobalBuy<span>.</span></a>
                    </div>
                    <p class="mb-4">{{ config('app.description', '連結全球好物，讓購物沒有國界。') }}</p>
                    <ul class="list-unstyled custom-social">
                        <li><a href="#"><span class="fa fa-brands fa-facebook-f"></span></a></li>
                        <li><a href="#"><span class="fa fa-brands fa-twitter"></span></a></li>
                        <li><a href="#"><span class="fa fa-brands fa-instagram"></span></a></li>
                        <li><a href="#"><span class="fa fa-brands fa-line"></span></a></li>
                    </ul>
                </div>
            </div>

            <div class="border-top copyright">
                <div class="row pt-4">
                    <div class="col-lg-6">
                        <p class="mb-2 text-center text-lg-start">
                            &copy; {{ date('Y') }} GlobalBuy. 版權所有。
                        </p>
                    </div>
                    <div class="col-lg-6 text-center text-lg-end">
                        <ul class="list-unstyled d-inline-flex ms-auto">
                            <li class="me-4"><a href="#">服務條款</a></li>
                            <li><a href="#">隱私政策</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/tiny-slider.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    @stack('scripts')
</body>
</html>
