<!DOCTYPE html>
<html>
<head>
    <title>搜尋結果</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>代購搜尋結果</h1>
        <p>總共找到：{{ $posts->total() }} 筆</p>
        
        @forelse($posts as $post)
            <div class="card mb-3">
                <div class="card-body">
                    <h5>{{ $post->title }}</h5>
                    <p>{{ $post->description }}</p>
                    <small>地區：{{ $post->region }}</small>
                </div>
            </div>
        @empty
            <div class="alert alert-info">
                沒有找到符合條件的代購貼文
            </div>
        @endforelse
        
        {{ $posts->links() }}
    </div>
</body>
</html>
