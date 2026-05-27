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
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use App\Http\Controllers\NotifyController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RequestListChatController;
use App\Http\Controllers\LogisticsController;
use App\Http\Controllers\ContentReportController;
use App\Http\Controllers\QuoteController;
use App\Events\MessageSent;
use App\Http\Controllers\FollowController; 
use App\Http\Controllers\FollowOrderController; 
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\HomeController;




Route::get('/agent/dashboard', [AgentDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('agent.dashboard');
    
// **權限控制**：您應該在路由或控制中加入檢查，確保只有 `status == 'approved'` 的使用者才能進入此頁面。
Route::get('/agent/notification/read/{buyer_id}/{request_list_id}', [App\Http\Controllers\RequestListController::class, 'readNotification'])
    ->middleware(['auth', 'verified']) // 👈 加上驗證中間件，確保安全
    ->name('agent.notification.read');

Route::get('/shop/store', [ShopController::class, 'store'])->name('store');

// 2. 新增 'shop.show'，解決追蹤名單與通知的錯誤
// 通常查看特定代購人需要帶 ID，所以加上 {id}
Route::get('/store/{id}', [ShopController::class, 'show'])->name('shop.show');

// 3. 確保語法是 [FollowController::class, '方法名'] 追蹤名單
Route::middleware(['auth'])->group(function () {
    Route::post('/follow/toggle', [FollowController::class, 'toggle'])->name('follow.toggle');
    Route::get('/follows', [FollowController::class, 'index'])->name('follows.index');
    
});

Route::get('/post-product-image/{postProduct}', [AgentPostController::class, 'image'])->name('post-product.image');
Route::get('/agent-post-cover-image/{agentPost}', [AgentPostController::class, 'coverImage'])->name('agent-post.cover-image');


Route::middleware(['auth'])->group(function () {
 
    // 請購單聊天室（請託人 & 代購人共用同一個頁面）
    Route::get('/request-list/{requestList}/chat',  [App\Http\Controllers\RequestListChatController::class, 'show'])->name('request-list.chat.show');
    Route::post('/request-list/{requestList}/chat', [App\Http\Controllers\RequestListChatController::class, 'send'])->name('request-list.chat.send');
    Route::post('/request-list/{requestList}/chat/read', [App\Http\Controllers\RequestListChatController::class, 'markRead'])->name('request-list.chat.read');
 
});

Route::get('/admin/dashboard', [AdminAuthController::class, 'dashboard'])->middleware('admin.auth')->name('admin.dashboard');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
Route::patch('/admin/agent-applications/{agentApplication}/approve', [AdminAuthController::class, 'approveAgentApplication'])->middleware('admin.auth')->name('admin.agent-applications.approve');
Route::patch('/admin/agent-applications/{agentApplication}/reject', [AdminAuthController::class, 'rejectAgentApplication'])->middleware('admin.auth')->name('admin.agent-applications.reject');

Route::get('/admin/agent-applications/user/{user}/identity-image/{side}', [AdminAuthController::class, 'identityImage'])
    ->middleware('admin.auth')
    ->name('admin.agent-applications.identity-image');
Route::get('/admin/request-items/{requestItem}/image', [AdminAuthController::class, 'requestItemImage'])
    ->middleware('admin.auth')
    ->name('admin.request-items.image');

Route::delete('/admin/request-lists/{requestList}', [AdminAuthController::class, 'deleteRequestList'])->middleware('admin.auth')->name('admin.request-lists.delete');
Route::delete('/admin/agent-posts/{agentPost}', [AdminAuthController::class, 'deleteAgentPost'])->middleware('admin.auth')->name('admin.agent-posts.delete');
Route::post('/request-list/{requestList}/chat-read', [RequestListChatController::class, 'markAsRead'])->name('request-list.chat.read');

Route::patch('/admin/reports/{report}/approve', [AdminAuthController::class, 'approveReport'])->middleware('admin.auth')->name('admin.reports.approve');
Route::patch('/admin/reports/{report}/reject', [AdminAuthController::class, 'rejectReport'])->middleware('admin.auth')->name('admin.reports.reject');

Route::patch('/admin/reports/{report}/override', [AdminAuthController::class, 'overrideDecision'])->middleware('admin.auth')->name('admin.reports.override');
Route::middleware('auth')->get('/api/latest-orders', [DashboardController::class, 'getLatestOrders'])->name('api.orders.latest');

Route::get('/', function (Request $request) {

    if ($request->filled('post_id') || $request->filled('search') || $request->filled('country')) {
        return app(HomeController::class)->search($request);
    }

 $totalOpenPosts = max(AgentPost::where('status', 'open')->count(), 1);

    $hotPosts = AgentPost::with(['user', 'products'])
        ->withCount(['favorites', 'orders'])
        ->where('status', 'open')
        ->get()
        ->map(function (AgentPost $post) use ($totalOpenPosts) {
            $favoriteRatio = min(($post->favorites_count / $totalOpenPosts) * 100, 100);
            $orderRatio = min(($post->orders_count / $totalOpenPosts) * 100, 100);

            $score = (int) round(($favoriteRatio * 0.55) + ($orderRatio * 0.45));
            $post->hot_score = max(0, min(100, $score));

            return $post;
        })
        ->sortByDesc('hot_score')
        ->take(6)
        ->values();

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

     return view('home', compact('agentPosts', 'hotPosts', 'requests', 'favoritedAgentPostIds'));
})->name('home');

    Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

     Route::post('/dashboard/request-lists/expired-notices/read', [DashboardController::class, 'markExpiredNoticeRead'])->name('dashboard.expired-notices.read');
     Route::post('/dashboard/request-lists/expired-notices/{requestList}/remove', [DashboardController::class, 'removeExpiredNotice'])->name('dashboard.expired-notices.remove');

    // 代購人會員專區
    Route::get('/agent/member', function (Request $request) {

        $user = Auth::user();

        $finishedOrdersCount = Order::where('seller_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $totalIncome = Order::where('seller_id', $user->id)
            ->where('status', 'completed')
            ->sum('total_amount');

        $agentHistorySearch = trim($request->query('agent_history_search', ''));

        $agentHistoryOrders = Order::with(['buyer', 'source'])
            ->where('seller_id', $user->id)
            ->where('status', 'completed')
            ->when($agentHistorySearch, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('order_no', 'like', "%{$search}%")
                        ->orWhereHas('buyer', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('source', function ($query) use ($search) {
                            $query->where('title', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('updated_at')
            ->get();

        return view('agent.member', compact('finishedOrdersCount', 'totalIncome', 'agentHistoryOrders', 'agentHistorySearch'));
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
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    // --- 聊天 API 路由 ---
    Route::get('/messages/search-users', [MessageController::class, 'searchUsers'])->name('messages.search-users');
    Route::get('/messages/{user}/history', [MessageController::class, 'history'])->name('messages.history');
    Route::post('/messages/send', [MessageController::class, 'send'])->name('messages.send');
    Route::post('/messages/{user}/read', [MessageController::class, 'markRead'])->name('messages.read');
    // --- 代購人專屬的聊天路由 ---
    Route::get('/agent/chat', [MessageController::class, 'agentIndex'])->name('agent.chat');
    
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
    Route::patch('/agent/posts/{agentPost}/ship', [AgentPostController::class, 'ship'])->name('agent.posts.ship');
    Route::patch('/agent/posts/{agentPost}/complete', [AgentPostController::class, 'complete'])->name('agent.posts.complete');
    Route::delete('/agent/posts/{agentPost}', [AgentPostController::class, 'destroy'])->name('agent.posts.destroy');
    Route::delete('/agent/orders/{order}/cancel', [AgentPostController::class, 'cancelBuyerOrder'])->name('agent.orders.cancel');

});

Route::middleware('auth')->group(function () {
    Route::get('/request-list/create', [RequestListController::class, 'create'])->name('request-list.create');
    Route::post('/request-list', [RequestListController::class, 'store'])->name('request-list.store');
    Route::put('/request-list/{requestList}', [RequestListController::class, 'update'])->name('request-list.update');
    Route::patch('/request-list/{requestList}/submit', [RequestListController::class, 'submit'])->name('request-list.submit');
    Route::patch('/request-list/{requestList}/complete', [RequestListController::class, 'complete'])->name('request-list.complete');
    Route::delete('/request-list/{requestList}', [RequestListController::class, 'destroy'])->name('request-list.destroy');
    Route::get('/request-item-image/{requestItem}', [RequestListController::class, 'image'])->name('request-item.image');
     Route::post('/dashboard/request-lists/{requestList}/quote-notices/read', [DashboardController::class, 'markQuoteNoticeRead'])->name('dashboard.quote-notices.read');

    // 聊天路由已統一在上方 auth 群組

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/shoppingcart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart/empty', [CartController::class, 'empty'])->name('cart.empty');
    Route::post('/agent-posts/{agentPost}/follow-order', [OrderController::class, 'store'])->name('orders.store');
    Route::post('/reports', [ContentReportController::class, 'store'])->name('reports.store');
});
Route::patch('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');

    //建立搜尋自己清單的路由
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/request-lists/expired-notices/read', [DashboardController::class, 'markExpiredNoticeRead'])->name('dashboard.expired-notices.read');
    Route::post('/dashboard/request-lists/expired-notices/{requestList}/remove', [DashboardController::class, 'removeExpiredNotice'])->name('dashboard.expired-notices.remove');
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


Route::middleware('auth')->group(function () {
    // --- 多人報價系統路由 ---
    // 1. 代購人提交報價
    Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store');
    
    // 2. 委託人接受特定報價 (這裡會觸發綁定 agent_id 並進入結帳流程)
    Route::post('/quotes/{quote}/accept', [QuoteController::class, 'accept'])->name('quotes.accept');
    
    // 3. (選配) 查看某個需求單的所有報價
    Route::get('/request-list/{requestList}/quotes', [QuoteController::class, 'index'])->name('quotes.index');
    // 通知中心
    Route::get('/dashboard/notifications', [NotifyController::class, 'index'])->name('notifications.index');
});


Route::post('/request-list/{requestList}/chat-read', [RequestListChatController::class, 'markAsRead'])->name('request-list.chat.read');







//物流設定路徑
Route::middleware(['auth'])->prefix('dashboard/settings')->group(function () {
    // 網址將會是 /dashboard/settings/logistics
    Route::get('/logistics', [LogisticsController::class, 'index'])->name('logistics.index');
    
    // 儲存功能 /dashboard/settings/logistics/save
    Route::post('/logistics/save', [LogisticsController::class, 'save'])->name('logistics.save');
});

//導入結帳功能

Route::get('/shopping-cart', [CartController::class, 'index'])->name('shopping.cart');

require __DIR__.'/auth.php';

// 1. 這是發送端：負責把訊息踢給 Pusher
Route::get('/test-broadcast', function () {
    broadcast(new MessageSent('測試人員', '哈囉！這是從我的電腦發出的即時訊息'));
    return "訊息已送出！請回測試網頁查看。";
});

// 2. 這是接收端：顯示畫面的網頁
Route::view('/chat-view', 'chat_test');

Route::middleware('auth')->group(function () {
    Route::post('/quotes', [App\Http\Controllers\QuoteController::class, 'store'])->name('quotes.store');
    Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
});

Route::middleware(['auth'])->group(function () {
    // 查看單一報價詳細內容
    Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    
    // 接受報價
    Route::post('/quotes/{quote}/accept', [QuoteController::class, 'accept'])->name('quotes.accept');
    
    // 拒絕報價
    Route::post('/quotes/{quote}/reject', [QuoteController::class, 'reject'])->name('quotes.reject');
    // 退回報價
    Route::post('/quotes/{quote}/return', [QuoteController::class, 'return'])->name('quotes.return');

    // 代購人修改已退回的報價
    Route::patch('/quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');

    // 出貨
    Route::patch('/quotes/{quote}/ship', [QuoteController::class, 'ship'])->name('quotes.ship');

    // 完成
    Route::patch('/quotes/{quote}/complete', [QuoteController::class, 'complete'])->name('quotes.complete');

});
//展開貼文
Route::get('/store', [AgentPostController::class, 'index'])->name('store.index');
//退回跟單
Route::delete('/order/cancel/{id}', [App\Http\Controllers\CartController::class, 'cancelOrder'])->name('order.cancel');
// 處理結帳提交
Route::post('/checkout/process', [App\Http\Controllers\CartController::class, 'processCheckout'])->name('checkout.process');
//結帳確認
// 確保這裡的 'processCheckout' 跟 Controller 裡寫的一模一樣
Route::post('/checkout/process', [App\Http\Controllers\CartController::class, 'processCheckout'])->name('checkout.process');

Route::middleware(['auth'])->group(function () {
    // 代購人歷史紀錄主路由
    Route::get('/agent/member', [HistoryController::class, 'agentHistory'])->name('agent.member');
});


// 🌟 2. 這是保留給舊有 Home（首頁）或其他地方呼叫的相容路由（別名）
// 網址一樣指向新的 /follow-order-submit，但名字保留舊的，這樣首頁就絕對不會報錯！
Route::middleware(['auth'])->group(function () {
    // 統一入口：現在前端表單只需要對準這個路由，帶入 agentPost 的 ID 即可
    Route::post('/agent-posts/{agentPost}/order', [App\Http\Controllers\OrderController::class, 'store'])
         ->name('order.store');
    // 購物車與其他邏輯
    Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');

});
Route::middleware(['auth'])->group(function () {
    // 確保這裡的名稱為 'order.store'
    Route::post('/agent-posts/{agentPost}/order', [OrderController::class, 'store'])
         ->name('order.store');
});