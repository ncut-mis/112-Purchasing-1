<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * 可批量賦值的欄位
     */
    protected $fillable = [
        'name', 
        'nickname', 
        'email', 
        'password', 
        'avatar', 
        'bio', 
        'purchasable_countries', 
        'role',
    ];

    /**
     * 隱藏欄位
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 屬性轉換
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // 確保從資料庫讀取時自動轉為 PHP 陣列，避免前端顯示亂碼
            'purchasable_countries' => 'array', 
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | 追蹤系統 (Following System)
    |--------------------------------------------------------------------------
    */

    /**
     * 我追蹤的人 (Following)
     */
    public function followings(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')->withTimestamps();
    }

    /**
     * 我的粉絲 (Followers)
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')->withTimestamps();
    }

    /**
     * 檢查是否已追蹤某人
     * 支援傳入 ID 或 User 實例
     */
    public function isFollowing($user): bool
    {
        $id = $user instanceof User ? $user->id : $user;
        
        if (!$id) return false;

        return $this->followings()->where('followed_id', $id)->exists();
    }

    /**
     * 切換追蹤狀態 (供 Controller 呼叫)
     * 回傳：['attached' => [id]] 代表新增追蹤, ['detached' => [id]] 代表取消追蹤
     */
    public function toggleFollow($user)
    {
        $id = $user instanceof User ? $user->id : $user;
        
        // 防呆：不能追蹤自己
        if ($this->id == $id) return false;

        return $this->followings()->toggle($id);
    }

    /*
    |--------------------------------------------------------------------------
    | 其他關聯與方法
    |--------------------------------------------------------------------------
    */

    /**
     * 收藏關聯
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * 代購申請紀錄
     */
    public function agentApplication(): HasOne
    {
        return $this->hasOne(AgentApplication::class);
    }

    /**
     * 檢查使用者是否為已認證的代購人 (導覽列判斷使用)
     */
    public function isApprovedAgent(): bool
    {
        return $this->agentApplication()->where('status', 'approved')->exists();
    }

    /**
     * 代購人發布的貼文
     */
    public function agentPosts(): HasMany
    {
        return $this->hasMany(AgentPost::class, 'user_id');
    }
}