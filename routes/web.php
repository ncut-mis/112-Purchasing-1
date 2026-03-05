<?php

use App\Http\Controllers\AgentApplicationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Models\Post;
use App\Models\PurchasingRequest;
use Illuminate\Support\Facades\Route;

Route::get('/agent/member', function() { 
    return view('agent.member'); 
})->name('agent.member');

Route::get('/agent/dashboard', function () {
    return view('agent.dashboard');
})->name('agent.dashboard');
// **權限控制**：您應該在路由或控制中加入檢查，確保只有 `status == 'approved'` 的使用者才能進入此頁面。

Route::get('/store', [ShopController::class, 'store'])->name('store');

Route::get('/', function () {
    $posts = Post::all();

    $requests = class_exists(PurchasingRequest::class)
        ? PurchasingRequest::all()
        : collect([]);

    return view('home', compact('posts', 'requests'));
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');



        // 聊天頁面
    Route::get('/chat', function () {
        return view('chat.index');
    })->name('chat.index');

    // 代購人申請功能

    Route::get('/agent/apply', [AgentApplicationController::class, 'create'])->name('agent.apply');
    Route::post('/agent/apply', [AgentApplicationController::class, 'store'])->name('agent.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/request-list/create', function () {
        return view('request-list.create');
    })->name('request-list.create');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/shoppingcart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/empty', [CartController::class, 'empty'])->name('cart.empty');
});

require __DIR__.'/auth.php';