<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; // 💡 記得引入 View
use Illuminate\Support\Facades\Auth; // 💡 記得引入 Auth
use App\Models\AgentNotification;   // 💡 記得引入你的通知 Model

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $user = \Illuminate\Support\Facades\Auth::user();
                
                // 撈出對應的未讀通知
                $agentNotifications = \App\Models\AgentNotification::where('agent_id', $user->id)
                                        ->where('is_read', false)
                                        ->with(['buyer', 'requestList'])
                                        ->latest() // 👈 確保最新送出的通知在最前面
                                        ->get();

                // 🎯 核心修正：依據 buyer_id 進行合併（同一個買家只保留最新的一筆）
                $groupedNotifications = $agentNotifications->unique('buyer_id');
                                        
                // 💡 把合併後的資料傳給前端 Blade
                $view->with('agentNotifications', $groupedNotifications);
            }
        });
    }
}