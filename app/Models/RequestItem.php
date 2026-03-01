<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    protected $fillable = [
        'request_list_id', 'name', 'reference_url', 'reference_image',
        'quantity', 'expected_price', 'specification'
    ];

    // 反向關聯回主表
    public function list()
    {
        return $this->belongsTo(RequestList::class, 'request_list_id');
    }
}