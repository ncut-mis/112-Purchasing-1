<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AgentApplicationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequestListController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\DashboardController;
use App\Models\Post;
use App\Models\PurchasingRequest;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 公開路由 (不需要登入)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $posts = Post::all();
    $requests = class_exists(PurchasingRequest::class) ? PurchasingRequest::all() : collect([]);
    return view('home', compact('posts', 'requests'));
})->name('home');

Route::get('/store', [ShopController::class, 'store'])->name('store');

<<<<<<< Updated upstream
// 管理員登入相關
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
    
    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', [AdminAuthController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
        Route::patch('/agent-applications/{agentApplication}/approve', [AdminAuthController::class, 'approveAgentApplication'])->name('admin.agent-applications.approve');
        Route::patch('/agent-applications/{agentApplication}/reject', [AdminAuthController::class, 'rejectAgentApplication'])->name('admin.agent-applications.reject');
        Route::delete('/request-lists/{requestList}', [AdminAuthController::class, 'deleteRequestList'])->name('admin.request-lists.delete');
    });
=======
        return view('dashboard', compact('requestLists'));
    })->name('dashboard');



        // 聊天頁面
    Route::get('/messages', function () {
        return view('messages.index');
    })->name('messages.index');

    // 代購人申請功能

    Route::get('/agent/apply', [AgentApplicationController::class, 'create'])->name('agent.apply');
    Route::post('/agent/apply', [AgentApplicationController::class, 'store'])->name('agent.store');
>>>>>>> Stashed changes
});

/*
|--------------------------------------------------------------------------
| 會員認證路由 (必須登入)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // 儀表板 (統一使用 Controller 處理邏輯，包含搜尋功能)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 代購人相關功能
    Route::prefix('agent')->group(function () {
        Route::get('/member', function() { return view('agent.member'); })->name('agent.member');
        Route::get('/dashboard', function () { return view('agent.dashboard'); })->name('agent.dashboard');
        
        // 申請功能
        Route::get('/apply', [AgentApplicationController::class, 'create'])->name('agent.apply');
        Route::post('/apply', [AgentApplicationController::class, 'store'])->name('agent.store');
        Route::get('/status', [AgentApplicationController::class, 'status'])->name('agent.status');
        
        // 個人資訊
        Route::get('/profile', function () { return view('agent.profile'); })->name('agent.profile.edit'); 
        Route::post('/profile', [AgentApplicationController::class, 'updateProfile'])->name('agent.profile.update');
    });

    // 請購清單管理
    Route::prefix('request-list')->group(function () {
        Route::get('/create', [RequestListController::class, 'create'])->name('request-list.create');
        Route::post('/', [RequestListController::class, 'store'])->name('request-list.store');
        Route::put('/{requestList}', [RequestListController::class, 'update'])->name('request-list.update');
    });
    Route::get('/request-item-image/{requestItem}', [RequestListController::class, 'image'])->name('request-item.image');

    // 個人帳號設定
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 購物車功能
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('cart.index');
        Route::post('/add', [CartController::class, 'add'])->name('cart.add');
        Route::patch('/update/{id}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
        Route::delete('/empty', [CartController::class, 'empty'])->name('cart.empty');
    });

    // 聊天與訊息
    Route::get('/chat', function () { return view('chat.index'); })->name('chat.index');
    Route::get('/messages', function () { return view('messages.index'); })->name('messages.index');
});

require __DIR__.'/auth.php';