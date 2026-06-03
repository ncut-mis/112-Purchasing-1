<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        'avatar',
    ];

    protected $appends = [
        'avatar_url',
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

    public function getAvatarUrlAttribute(): string
    {
        if (empty($this->avatar)) {
            return $this->generatePlaceholderAvatar();
        }

        if (is_string($this->avatar) && preg_match('#^(https?://|data:image/)#i', $this->avatar)) {
            return $this->avatar;
        }

        if (is_string($this->avatar) && $this->isPublicStoragePath($this->avatar)) {
            $path = $this->normalizePublicStoragePath($this->avatar);
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('public');
            return $disk->url($path);
        }

        if (is_string($this->avatar)) {
            $mime = $this->detectImageMimeType($this->avatar) ?? 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode($this->avatar);
        }

        return $this->generatePlaceholderAvatar();
    }

    private function isPublicStoragePath(string $value): bool
    {
        return preg_match('#^(?:/)?(?:storage/|public/)?[\w\-./]+\.[a-z0-9]{2,4}$#i', $value) === 1;
    }

    private function normalizePublicStoragePath(string $value): string
    {
        $normalized = ltrim($value, '/');

        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = Str::after($normalized, 'storage/');
        }

        if (Str::startsWith($normalized, 'public/')) {
            $normalized = Str::after($normalized, 'public/');
        }

        return $normalized;
    }

    private function detectImageMimeType(string $value): ?string
    {
        if (! function_exists('finfo_open')) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if (! $finfo) {
            return null;
        }

        $mime = finfo_buffer($finfo, $value);
        finfo_close($finfo);

        return $mime;
    }

    private function generatePlaceholderAvatar(): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name ?? 'User') . '&background=10b981&color=fff';
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