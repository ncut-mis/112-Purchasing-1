<?php

namespace App\Models;

// 關鍵：這行絕對不能錯，它是 Laravel 驗證系統的核心
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 管理員模型
 * 必須繼承 Authenticatable (即 User 類別) 才能支援 Auth::logout()
 */
class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];
}