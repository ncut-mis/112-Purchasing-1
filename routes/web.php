<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AgentApplicationController;
use App\Http\Controllers\AgentDashboardController;
use App\Http\Controllers\AgentPostController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RequestListController;
use App\Http\Controllers\ShopController;
use App\Models\AgentPost;
use App\Models\PurchasingRequest;
use App\Models\RequestList;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use App\Http\Controllers\NotifyController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RequestListChatController;
use App\Http\Controllers\LogisticsController;



Route::get('/agent/dashboard', [AgentDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('agent.dashboard');
// **權限控制**：您應該在路由或控制中加入檢查，確保只有 `status == 'approved'` 的使用者才能進入此頁面。

Route::get('/store', [ShopController::class, 'store'])->name('store');

Route::get('/post-product-image/{postProduct}', [AgentPostController::class, 'image'])->name('post-product.image');


Route::get('/admin/dashboard', [AdminAuthController::class, 'dashboard'])->middleware('admin.auth')->name('admin.dashboard');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
Route::patch('/admin/agent-applications/{agentApplication}/approve', [AdminAuthController::class, 'approveAgentApplication'])->middleware('admin.auth')->name('admin.agent-applications.approve');
Route::patch('/admin/agent-applications/{agentApplication}/reject', [AdminAuthController::class, 'rejectAgentApplication'])->middleware('admin.auth')->name('admin.agent-applications.reject');

Route::get('/admin/agent-applications/{agentApplication}/identity-image/{side}', [AdminAuthController::class, 'identityImage'])
    ->middleware('admin.auth')
    ->name('admin.agent-applications.identity-image');
Route::get('/admin/request-items/{requestItem}/image', [AdminAuthController::class, 'requestItemImage'])
    ->middleware('admin.auth')
    ->name('admin.request-items.image');

Route::delete('/admin/request-lists/{requestList}', [AdminAuthController::class, 'deleteRequestList'])->middleware('admin.auth')->name('admin.request-lists.delete');
Route::delete('/admin/agent-posts/{agentPost}', [AdminAuthController::class, 'deleteAgentPost'])->middleware('admin.auth')->name('admin.agent-posts.delete');
Route::middleware('auth')->get('/api/latest-orders', [DashboardController::class, 'getLatestOrders'])->name('api.orders.latest');

Route::get('/', function () {
    $agentPosts = AgentPost::with(['user', 'products'])
        ->where('status', 'open')
        ->latest()
        ->take(6)
        ->get();

    $favoritedAgentPostIds = auth()->check()
        ? auth()->user()->favorites()
            ->where('favoriteable_type', AgentPost::class)
            ->pluck('favoriteable_id')
            ->map(fn ($id) => (int) $id)
            ->all()
        : [];

    $requests = class_exists(PurchasingRequest::class)
        ? PurchasingRequest::all()
        : collect([]);

    return view('home', compact('agentPosts', 'requests', 'favoritedAgentPostIds'));
})->name('home');

    Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 代購人會員專區
    Route::get('/agent/member', function () {
    $user = Auth::user();
    // 1. 取得該代購人處理中的訂單數
    $finishedOrdersCount = RequestList::where('user_id', $user->id) 
        ->where('status', 'completed')
        ->count();
    // 2. 修正點：暫時將累計收入設為 0
    // 錯誤原因：您的資料表中沒有 total_price 欄位。
    // 如果您有其他代表金額的欄位（例如 budget），請將 'total_price' 改為該名稱。
    // 如果還沒有金額欄位，請先保留為 0 以避免頁面報錯。
    $totalIncome = 0; 
    /* 如果您確定了欄位名稱，可以取消下方註解並修改欄位名：
    $totalIncome = RequestList::where('user_id', $user->id)
        ->where('status', 'completed')
        ->sum('budget'); // 假設欄位叫 budget
    */
    return view('agent.member', compact('finishedOrdersCount', 'totalIncome'));
})->name('agent.member')->middleware(['auth', 'verified']);

     // 1. 申請頁面
    Route::get('/agent/apply', [AgentApplicationController::class, 'create'])->name('agent.apply');
    Route::post('/agent/apply', [AgentApplicationController::class, 'store'])->name('agent.store');
    // 2. 關鍵：新增申請進度查詢路由
    Route::get('/agent/status', [AgentApplicationController::class, 'status'])->name('agent.status');
    // 顯示個人資訊編輯頁面 (GET123)
    Route::get('/agent/profile', function () {
        return view('agent.profile');
    })->name('agent.profile.edit'); 
    // 處理個人資訊更新 (POST)
    Route::post('/agent/profile', [AgentApplicationController::class, 'updateProfile'])->name('agent.profile.update');
    // --- 請購人/一般會員的聊天路由 ---
    Route::get('/messages', function () {
    return view('messages.index');
    })->name('messages.index');
    // --- 代購人專屬的聊天路由 ---
    Route::get('/agent/chat', function () {
    return view('agent.chat'); // 指向 resources/views/agent/chat.blade.php
    })->name('agent.chat');
    
    // 查看代購人詳細資料
    Route::get('/agent/{id}/profile', [ShopController::class, 'show'])->name('agent.show');

    // 代購人申請功能

    Route::get('/agent/apply', [AgentApplicationController::class, 'create'])->name('agent.apply');
    Route::post('/agent/apply', [AgentApplicationController::class, 'store'])->name('agent.store');

    // 代購人貼文建立
    Route::get('/agent/posts/create', [AgentPostController::class, 'create'])->name('agent.posts.create');
    Route::post('/agent/posts', [AgentPostController::class, 'store'])->name('agent.posts.store');
    Route::patch('/agent/posts/{agentPost}', [AgentPostController::class, 'update'])->name('agent.posts.update');

    Route::patch('/agent/posts/{agentPost}/submit', [AgentPostController::class, 'submit'])->name('agent.posts.submit');
    Route::delete('/agent/posts/{agentPost}', [AgentPostController::class, 'destroy'])->name('agent.posts.destroy');

});

Route::middleware('auth')->group(function () {
    Route::get('/request-list/create', [RequestListController::class, 'create'])->name('request-list.create');
    Route::post('/request-list', [RequestListController::class, 'store'])->name('request-list.store');
    Route::put('/request-list/{requestList}', [RequestListController::class, 'update'])->name('request-list.update');
    Route::patch('/request-list/{requestList}/submit', [RequestListController::class, 'submit'])->name('request-list.submit');
    Route::delete('/request-list/{requestList}', [RequestListController::class, 'destroy'])->name('request-list.destroy');
    Route::get('/request-item-image/{requestItem}', [RequestListController::class, 'image'])->name('request-item.image');

    Route::get('/request-list/{requestList}/chat', [RequestListChatController::class, 'show'])->name('request-list.chat.show');
    Route::post('/request-list/{requestList}/chat', [RequestListChatController::class, 'store'])->name('request-list.chat.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/shoppingcart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/empty', [CartController::class, 'empty'])->name('cart.empty');
    Route::post('/agent-posts/{agentPost}/follow-order', [OrderController::class, 'store'])->name('orders.store');
});

    //建立搜尋自己清單的路由
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});



// 收藏/取消收藏請購清單
Route::middleware(['auth'])->post('/favorite/toggle', [\App\Http\Controllers\FavoriteController::class, 'toggle'])->name('favorite.toggle');


// 改用 HomeController 的 search 方法
Route::get('/agent/posts/search', [App\Http\Controllers\HomeController::class, 'search'])
    ->name('agent.posts.search');

Route::middleware('auth')->group(function () {
    // ...
    Route::post('/agent-posts/{agentPost}/follow-order', [OrderController::class, 'store'])->name('orders.store');
});

Route::post('/request-lists/agent-quote', [RequestListController::class, 'submitAgentQuote']);
Route::get('/dashboard/notifications', [NotifyController::class, 'index'])->name('notifications.index');


//拒絕的路徑
// 確保是 POST 方法，並且 ID 對應到你的需求單
Route::post('/request-chat/{id}/reject', [RequestListChatController::class, 'reject'])
    ->name('request.chat.reject');

//物流設定路徑
Route::middleware(['auth'])->prefix('dashboard/settings')->group(function () {
    // 網址將會是 /dashboard/settings/logistics
    Route::get('/logistics', [LogisticsController::class, 'index'])->name('logistics.index');
    
    // 儲存功能 /dashboard/settings/logistics/save
    Route::post('/logistics/save', [LogisticsController::class, 'save'])->name('logistics.save');
});

//導入結帳功能

Route::get('/shopping-cart', function () {
    // 使用 collect() 將空陣列轉換為 Laravel 集合物件
    $cartItems = collect([]); 

    return view('shop.shoppingcart', [
        'cartItems' => $cartItems
    ]); 
})->name('shopping.cart');

require __DIR__.'/auth.php';