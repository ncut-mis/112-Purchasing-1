<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /**
     * 取得使用者的所有收藏（多型）
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * 判斷是否已收藏指定請購清單
     */
    public function hasFavoritedRequestList($requestListId)
    {
        return $this->favorites()
            ->where('favoriteable_type', 'App\\Models\\RequestList')
            ->where('favoriteable_id', $requestListId)
            ->exists();
    }

    /**
     * 判斷是否已收藏指定代購貼文
     */
    public function hasFavoritedAgentPost($agentPostId)
    {
        return $this->favorites()
            ->where('favoriteable_type', 'App\\Models\\AgentPost')
            ->where('favoriteable_id', $agentPostId)
            ->exists();
    }
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    /**
     * 取得使用者的代購申請紀錄
     */
    public function agentApplication()
    {
        return $this->hasOne(AgentApplication::class);
    }

    /**
     * 檢查使用者是否為已認證的代購人
     */
    public function isApprovedAgent(): bool
    {
        // 檢查是否有申請紀錄，且狀態為 'approved'
        return $this->agentApplication()->where('status', 'approved')->exists();
    }

    public function agentPosts()
    {
    return $this->hasMany(AgentPost::class, 'user_id');
    }
}