<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestOffer extends Model
{
    protected $fillable = [
        'request_list_id', 'agent_user_id', 'offered_price', 
        'currency', 'delivery_date', 'message', 'status'
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    // 關聯：這是對哪張單的報價
    public function requestList()
    {
        return $this->belongsTo(RequestList::class);
    }

    // 關聯：這是誰報的價
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_user_id');
    }

    /**
     * 檢查這個報價是否被接受
     */
    public function isAccepted()
    {
        return $this->status === 'accepted';
    }
}