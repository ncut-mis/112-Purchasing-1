@extends('layouts.front')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-1">管理員後台</h2>
                    <p class="text-muted mb-0">歡迎，{{ $adminName }}。</p>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger rounded-pill px-4">登出管理員</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
