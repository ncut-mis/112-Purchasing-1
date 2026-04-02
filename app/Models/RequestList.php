<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestList extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'country', 'city', 'deadline',
        'budget_total', 'currency', 'status', 'detail_address', 'note','people','time'
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    // 關聯：這張單包含哪些商品
    public function items()
    {
        return $this->hasMany(RequestItem::class);
    }

    // 關聯：這張單收到了哪些代購人的報價
    public function offers()
    {
        return $this->hasMany(RequestOffer::class);
    }

    // 關聯：發起人
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}