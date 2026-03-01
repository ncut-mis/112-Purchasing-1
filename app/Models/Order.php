<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_no', 'buyer_id', 'seller_id', 'source_type', 'source_id',
        'items_total', 'shipping_fee', 'platform_fee', 'total_amount', 'currency',
        'status', 'payment_method', 'paid_at',
        'shipping_method', 'tracking_number', 'shipped_at',
        'recipient_data', 'note'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'recipient_data' => 'array', // 自動將 JSON 轉為 PHP Array
    ];

    // 關聯：買家
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // 關聯：賣家
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // 關聯：訂單來源 (多型關聯)
    public function source()
    {
        return $this->morphTo();
    }

    // 關聯：訂單明細
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    // 輔助方法：產生訂單編號
    public static function generateOrderNo()
    {
        // 格式範例: ORD + 年月日 + 隨機碼 -> ORD20231027AB12
        return 'ORD' . date('Ymd') . strtoupper(substr(uniqid(), -4));
    }
}