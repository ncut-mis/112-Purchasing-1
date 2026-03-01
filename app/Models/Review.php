<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['order_id', 'reviewer_id', 'reviewee_id', 'rating', 'comment'];

    // 關聯：訂單
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // 關聯：評價人
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // 關聯：被評價人
    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }