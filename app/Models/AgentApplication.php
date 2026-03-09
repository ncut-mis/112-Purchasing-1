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
        'name',
        'country',
        'phone',
        'main_region',
        'experience',
        'id_number',
        'id_image_front',
        'id_image_back',
        'status',
        'admin_remark',
    ];

    /**
     * 取得申請的會員
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}