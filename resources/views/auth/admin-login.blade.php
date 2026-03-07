@extends('layouts.front')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="fw-bold mb-2">管理員登入</h2>
                        <p class="text-muted mb-4">此入口僅供平台管理員使用。</p>

                        <form method="POST" action="{{ route('admin.login.submit') }}">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label fw-semibold">管理員帳號</label>
                                <input type="text" name="username" value="{{ old('username') }}" class="form-control @error('username') is-invalid @enderror" placeholder="請輸入管理員帳號">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">密碼</label>
                                <input type="password" name="password" class="form-control" placeholder="請輸入密碼">
                            </div>

                            <button type="submit" class="btn btn-dark w-100 rounded-pill">登入管理後台</button>
                        </form>

                        <div class="mt-3 text-center">
                            <a href="{{ route('home') }}" class="text-muted text-decoration-none">返回首頁</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
