<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\Post;
use App\Models\PurchasingRequest;
use App\Http\Controllers\AgentApplicationController;

Route::get('/', function () {
    // 抓取代購貼文
    $posts = Post::all();

    // 抓取請購清單 (如果還沒做 Model，暫時用 collect([]) 代替)
    $requests = class_exists(PurchasingRequest::class)
        ? PurchasingRequest::all()
        : collect([]);

    return view('home', compact('posts', 'requests'));
})->name('home');

// 需要登入且驗證過的路由
Route::middleware(['auth', 'verified'])->group(function () {
    // 會員控制台
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // 代購人申請功能
    Route::get('/agent/apply', [AgentApplicationController::class, 'create'])->name('agent.apply');
    Route::post('/agent/apply', [AgentApplicationController::class, 'store'])->name('agent.store');
});

// 其他會員相關路由 (個人資料)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';