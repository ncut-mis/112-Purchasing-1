@extends('layouts.front')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-5 text-center">
                        <h2 class="fw-bold mb-3">管理員登入</h2>
                        <p class="text-muted mb-4">管理員專用登入畫面尚在設計中，敬請期待。</p>
                        <a href="{{ route('home') }}" class="btn btn-success rounded-pill px-4">返回首頁</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
