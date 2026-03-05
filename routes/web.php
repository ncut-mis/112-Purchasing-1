<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopController;
use App\Models\Post;
use App\Models\PurchasingRequest;
use App\Http\Controllers\CartController;


Route::get('/store', [ShopController::class, 'store'])->name('store');
Route::get('/', function () {
    // 抓取代購貼文
    $posts = Post::all(); 
    
    // 抓取請購清單 (如果還沒做 Model，暫時用 collect([]) 代替)
    $requests = class_exists(PurchasingRequest::class) 
                ? PurchasingRequest::all() 
                : collect([]);

    return view('home', compact('posts', 'requests'));
})->name('home');




    //登入後才能訪問dashboard頁面
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    //編輯個人檔案頁面
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    //更新個人檔案
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    //刪除帳號
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    // 顯示購物車頁面
    Route::get('/shoppingcart', [CartController::class, 'index'])->name('cart.index');
    
    // 加入購物車
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    
    // 更新購物車數量
    Route::patch('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
    
    // 移除購物車項目
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    
    // 清空購物車（可選）
    Route::delete('/cart/empty', [CartController::class, 'empty'])->name('cart.empty');
});

    //登入後才能訪問購物車頁面
Route::middleware(['auth'])->group(function () {
    Route::get('/shoppingcart', [CartController::class, 'index'])->name('cart.index');
});
require __DIR__.'/auth.php';    
