<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


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

    public function getIdImageFrontUrlAttribute(): ?string
    {
        return $this->resolveIdentityImageUrl($this->id_image_front);
    }

    public function getIdImageBackUrlAttribute(): ?string
    {
        return $this->resolveIdentityImageUrl($this->id_image_back);
    }

    private function resolveIdentityImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', 'data:image'])) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = Str::after($normalized, 'storage/');
        }

        if (Str::startsWith($normalized, 'public/')) {
            $normalized = Str::after($normalized, 'public/');
        }

        return Storage::disk('public')->url($normalized);
    }
}
