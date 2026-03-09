<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AgentApplicationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequestListController;
use App\Http\Controllers\ShopController;
use App\Models\Post;
use App\Models\PurchasingRequest;
use App\Models\RequestList;
use Illuminate\Support\Facades\Route;

Route::get('/agent/member', function() { 
    return view('agent.member'); 
})->name('agent.member');

Route::get('/agent/dashboard', function () {
    return view('agent.dashboard');
})->name('agent.dashboard');
// **權限控制**：您應該在路由或控制中加入檢查，確保只有 `status == 'approved'` 的使用者才能進入此頁面。

Route::get('/store', [ShopController::class, 'store'])->name('store');

Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::get('/admin/dashboard', [AdminAuthController::class, 'dashboard'])->middleware('admin.auth')->name('admin.dashboard');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::get('/', function () {
    $posts = Post::all();

    $requests = class_exists(PurchasingRequest::class)
        ? PurchasingRequest::all()
        : collect([]);

    return view('home', compact('posts', 'requests'));
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $requestLists = RequestList::with('items')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('dashboard', compact('requestLists'));
    })->name('dashboard');

    // 1. 代購人會員專區首頁
    Route::get('/agent/member', function () {
        return view('agent.member');
    })->name('agent.member');

    // 2. 顯示編輯個人資訊頁面 (GET)
    Route::get('/agent/profile', function () {
        return view('agent.profile');
    })->name('agent.profile.edit');

    // 3. 接收並處理更新後的資訊 (POST)
    // 這裡我們直接導向 Controller 來處理圖片與文字儲存
    Route::post('/agent/profile', [AgentApplicationController::class, 'updateProfile'])->name('agent.profile.update');


        // 聊天頁面
    Route::get('/chat', function () {
        return view('chat.index');
    })->name('chat.index');

    // 代購人申請功能

    Route::get('/agent/apply', [AgentApplicationController::class, 'create'])->name('agent.apply');
    Route::post('/agent/apply', [AgentApplicationController::class, 'store'])->name('agent.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/request-list/create', [RequestListController::class, 'create'])->name('request-list.create');
    Route::post('/request-list', [RequestListController::class, 'store'])->name('request-list.store');
    Route::put('/request-list/{requestList}', [RequestListController::class, 'update'])->name('request-list.update');
    Route::get('/request-item-image/{requestItem}', [RequestListController::class, 'image'])->name('request-item.image');

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