<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    /**
     * 允許批量寫入的欄位 (對應你的資料庫欄位)
     */
    protected $fillable = [
        'user_id',      // 發佈者 ID
        'title',        // 貼文標題
        'description',  // 詳細資訊
        'region',       // 地區 (需求三-3)
        'category',     // 商品類別
        'max_quantity', // 最大數量 (需求三-7)
        'deadline',     // 代購截止日期 (需求三-7)
        'status',       // 狀態 (例如：募集中、已結束)
    ];

    /**
     * 取得發佈此貼文的會員 (代購人)
     * 關聯到 User 模型
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 格式化日期顯示 (選用)
     */
    protected $casts = [
        'deadline' => 'datetime',
    ];
}