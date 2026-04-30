<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    use HasFactory;

    // 如果資料庫表名確定的話，建議與資料庫實際名稱一致（例如 'quotes' 或 'Quote'）
    protected $table = 'quotes';

    protected $fillable = [
        'request_list_id',
        'user_id',
        'price',
        'comment',
        'status',
        'items_details',
    ];

    /**
     * 取得提交此報價的代購人 (使用者)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 取得此報價對應的需求清單 (只保留這一個定義)
     */
    public function requestList(): BelongsTo
    {
        return $this->belongsTo(RequestList::class, 'request_list_id');
    }
}