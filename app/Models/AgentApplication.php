<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'ID_Card',
        'status',
    ];

    /**
     * 取得申請的會員
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}