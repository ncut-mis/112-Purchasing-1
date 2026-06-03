<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentNotification extends Model
{
    protected $fillable = [
    'agent_id', 'buyer_id', 'request_list_id', 'title', 'content', 'is_read', 'is_selected'];

    // 關聯請購人 (買家)
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // 關聯請託單
    public function requestList()
    {
        return $this->belongsTo(RequestList::class, 'request_list_id');
    }
}