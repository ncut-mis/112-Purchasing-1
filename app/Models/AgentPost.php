<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentPost extends Model
{
    use HasFactory, SoftDeletes;

    // 允許批量賦值的欄位 (安全性設定)
    protected $fillable = [
        'user_id',
        'title',
        'country',
        'city',
        'description',
        'start_date',
        'end_date',
        'estimated_shipping_date',
        'status',
        'cover_image',
    ];

    // 自動將日期欄位轉換為 Carbon 物件，方便前端做日期運算或格式化
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'estimated_shipping_date' => 'date',
    ];

    // 關聯設定：這篇貼文屬於哪個使用者 (代購人)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 關聯設定：這篇貼文包含哪些具體商品 (PostProduct)
    // 雖然貼文是主體，但具體可買的商品 (如：藥妝、公仔) 是分開存的
    public function products()
    {
        return $this->hasMany(PostProduct::class);
    }
}