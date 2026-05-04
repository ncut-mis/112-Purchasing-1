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

    protected $fillable = [
        'name', 'nickname', 'email', 'password', 'avatar', 'bio', 'purchasable_countries', 'role',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'purchasable_countries' => 'array', 
        ];
    }

    /**
     * 我追蹤的人 (Following)
     */
    public function followings(): BelongsToMany
    {
        // 確保 table 名稱與 migration 一致 (通常是 follows)
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
     * 切換追蹤狀態 (精簡 Controller 邏輯用)
     * 回傳：['attached' => [id]] 代表新增追蹤, ['detached' => [id]] 代表取消追蹤
     */
    public function toggleFollow($user)
    {
        $id = $user instanceof User ? $user->id : $user;
        
        // 自己不能追蹤自己
        if ($this->id == $id) return false;

        return $this->followings()->toggle($id);
    }

    /* --- 原有其他關聯與方法 --- */

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function agentApplication(): HasOne
    {
        return $this->hasOne(AgentApplication::class);
    }

    public function isApprovedAgent(): bool
    {
        return $this->agentApplication()->where('status', 'approved')->exists();
    }

    public function agentPosts(): HasMany
    {
        return $this->hasMany(AgentPost::class, 'user_id');
    }
}