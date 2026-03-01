<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'name', 'options',
        'price', 'quantity', 'subtotal'
    ];

    // 關聯：屬於哪張訂單
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}