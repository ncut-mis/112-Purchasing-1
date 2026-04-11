<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logistics extends Model
{
    protected $fillable = [
        'name',
        'status',
        'ship_type',
        'payment_method',
        'available_times',
        'temp_layer',
    ];

    // 自動將資料庫的 JSON 字串轉換為 PHP 陣列，方便 Blade 直接使用
    protected $casts = [
        'available_times' => 'array',
        'status' => 'boolean',
    ];
}