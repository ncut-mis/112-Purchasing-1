<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController; // 記得引入這行
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ★★★ 這是我們自訂的首頁，請確保這行在最上面 ★★★
Route::get('/', [HomeController::class, 'index'])->name('home');

// 這是 Breeze 預設的後台首頁 (登入後會來這)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 這是 Breeze 預設的個人資料路由
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ★★★ 這行就是原本報錯找不到的檔案，現在安裝後應該有了 ★★★
require __DIR__.'/auth.php';