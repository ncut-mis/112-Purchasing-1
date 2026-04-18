<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ModerationModeService
{
    private const CACHE_KEY = 'content_review_mode';
    public const MODE_MANUAL = 'manual';
    public const MODE_AUTO = 'auto';

    public function getMode(): string
    {
        $mode = (string) Cache::get(self::CACHE_KEY, self::MODE_MANUAL);

        return in_array($mode, [self::MODE_MANUAL, self::MODE_AUTO], true)
            ? $mode
            : self::MODE_MANUAL;
    }

    public function isAuto(): bool
    {
        return $this->getMode() === self::MODE_AUTO;
    }

    public function setMode(string $mode): void
    {
        $resolvedMode = in_array($mode, [self::MODE_MANUAL, self::MODE_AUTO], true)
            ? $mode
            : self::MODE_MANUAL;

        Cache::forever(self::CACHE_KEY, $resolvedMode);
    }

    public function modeLabel(?string $mode = null): string
    {
        return ($mode ?? $this->getMode()) === self::MODE_AUTO
            ? '自動審核'
            : '人工審核';
    }
}
