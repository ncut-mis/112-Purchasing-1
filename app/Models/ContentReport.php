<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentReport extends Model
{
    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'report_type',
        'reason',
        'status',
        'reviewed_by_admin_id',
        'review_note',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id');
    }

    public function reportable(): MorphTo
    {
        $relation = $this->morphTo();

        return method_exists($relation, 'withTrashed') ? $relation->withTrashed() : $relation;
    }

    public function removeReportableFromPublicListings(): void
    {
        $reportable = $this->reportable;

        if (! $reportable || ! in_array(SoftDeletes::class, class_uses_recursive($reportable), true)) {
            return;
        }

        if ($reportable instanceof RequestList) {
            $reportable->forceFill([
                'violation_notified_at' => now(),
                'violation_notice_read_at' => null,
                'violation_notice_removed_at' => null,
            ])->save();
        }

        if (! $reportable->trashed()) {
            $reportable->delete();
        }
    }

    public function restoreReportableToPublicListings(): void
    {
        $reportable = $this->reportable;

        if (! $reportable || ! in_array(SoftDeletes::class, class_uses_recursive($reportable), true)) {
            return;
        }

        if ($reportable->trashed()) {
            $reportable->restore();
        }

        if ($reportable instanceof RequestList) {
            $reportable->forceFill([
                'violation_notified_at' => null,
                'violation_notice_read_at' => null,
                'violation_notice_removed_at' => null,
            ])->save();
        }
    }

    public static function typeLabel(string $type): string
    {
        return [
            'false_info' => '虛假資訊',
            'fraud' => '詐騙嫌疑',
            'prohibited_items' => '違禁品',
            'copyright' => '侵權內容',
            'harassment' => '騷擾或威脅',
            'spam' => '垃圾訊息',
            'other' => '其他',
        ][$type] ?? $type;
    }
}