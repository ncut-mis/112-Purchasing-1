<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\AgentApplication;
use App\Models\ContentReport;
use App\Models\User;

    class RequestList extends Model
    {
        use SoftDeletes;

        protected $fillable = [
            'user_id', 'title', 'store_name', 'country', 'deadline',
           'budget_total', 'currency', 'status', 'expired_notified_at', 'expired_notice_read_at', 'expired_notice_removed_at', 'violation_notified_at', 'violation_notice_read_at', 'violation_notice_removed_at', 'quote_notice_seen_count', 'detail_address', 'note', 'people', 'time', 'agent_quote_total'
        ];

        protected $casts = [
            'deadline' => 'date',
            'expired_notified_at' => 'datetime',
            'expired_notice_read_at' => 'datetime',
            'expired_notice_removed_at' => 'datetime',
            'violation_notified_at' => 'datetime',
            'violation_notice_read_at' => 'datetime',
            'violation_notice_removed_at' => 'datetime',
            'quote_notice_seen_count' => 'integer',
        ];

        // 關聯：這張單包含哪些商品
        public function items()
        {
            return $this->hasMany(RequestItem::class);
        }

        // 關聯：這張單收到了哪些代購人的報價
        public function offers()
        {
            return $this->hasMany(RequestOffer::class);
        }

        // 關聯：這張單收到的代購報價（Quote）
        public function quotes()
        {
            return $this->hasMany(Quote::class);
        }

        // 關聯：發起人
        public function user()
        {
            return $this->belongsTo(User::class, 'user_id');
        }
        public function agent()
        {
            // 現在 people 是數字了，這座橋樑會非常穩固！
            return $this->belongsTo(User::class, 'people', 'id');
        }

        public function reports()
        {
            return $this->morphMany(ContentReport::class, 'reportable');
        }
    }