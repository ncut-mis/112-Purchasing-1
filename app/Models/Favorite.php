<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = ['user_id', 'favoriteable_id', 'favoriteable_type'];

    // 關聯：收藏的目標 (多型)
    public function favoriteable()
    {
        return $this->morphTo();
    }

    // 關聯：誰收藏的
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
