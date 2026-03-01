<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'post_product_id', 'quantity'];

    // 關聯：商品
    public function product()
    {
        return $this->belongsTo(PostProduct::class, 'post_product_id');
    }

    // 關聯：用戶
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}