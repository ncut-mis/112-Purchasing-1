<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'followed_id',
    ];

    /**
     * 取得追蹤者資訊
     */
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    /**
     * 取得被追蹤者 (代購人) 資訊
     */
    public function followed()
    {
        return $this->belongsTo(User::class, 'followed_id');
    }
}