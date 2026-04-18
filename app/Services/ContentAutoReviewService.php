<?php

namespace App\Services;

use App\Models\AgentPost;
use App\Models\ContentReport;
use App\Models\RequestList;

class ContentAutoReviewService
{
    /**
     * 根據檢舉類型與內容關鍵字做基礎比對，回傳是否成立。
     */
    public function shouldApprove(ContentReport $report): bool
    {
        $reportable = $report->reportable;
        if (! $reportable) {
            return false;
        }

        $contentText = $this->collectContentText($reportable);
        $reason = mb_strtolower((string) $report->reason);
        $reportType = (string) $report->report_type;

        $keywords = $this->keywordsByType($reportType);

        $matchedContent = $this->matchesAnyKeyword($contentText, $keywords);
        $matchedReason = $this->matchesAnyKeyword($reason, $keywords);

        if ($matchedContent && $matchedReason) {
            return true;
        }

        // 若內容有高度疑慮關鍵字，即使檢舉理由沒有命中，仍判定成立
        $highRisk = ['詐騙', '假貨', '毒品', '仿冒', '盜版', '騷擾', '人身攻擊'];
        return $this->matchesAnyKeyword($contentText, $highRisk) && $reportType !== 'other';
    }

    private function collectContentText(mixed $reportable): string
    {
        $fragments = [];

        if ($reportable instanceof RequestList) {
            $reportable->loadMissing('items');
            $fragments[] = (string) $reportable->title;
            $fragments[] = (string) $reportable->country;
            $fragments[] = (string) $reportable->note;
            $fragments[] = (string) $reportable->detail_address;

            foreach ($reportable->items as $item) {
                $fragments[] = (string) $item->name;
                $fragments[] = (string) $item->specification;
                $fragments[] = (string) $item->reference_url;
                $fragments[] = (string) $item->reference_image;
            }
        }

        if ($reportable instanceof AgentPost) {
            $reportable->loadMissing('products');
            $fragments[] = (string) $reportable->title;
            $fragments[] = (string) $reportable->country;
            $fragments[] = (string) $reportable->description;

            foreach ($reportable->products as $product) {
                $fragments[] = (string) $product->name;
                $fragments[] = (string) $product->image_path;
            }
        }

        return mb_strtolower(implode(' ', array_filter($fragments, fn ($value) => trim((string) $value) !== '')));
    }

    private function keywordsByType(string $reportType): array
    {
        return match ($reportType) {
            'false_info' => ['假貨', '誇大', '不實', '偽造', '詐稱'],
            'fraud' => ['詐騙', '騙', '假交易', '捲款', '盜刷'],
            'prohibited_items' => ['毒品', '槍枝', '違禁', '走私', '菸酒', '管制藥品'],
            'copyright' => ['盜版', '侵權', '未授權', '仿冒', '抄襲'],
            'harassment' => ['騷擾', '辱罵', '人身攻擊', '威脅', '歧視'],
            'spam' => ['廣告', '洗版', '重複貼文', '引流', 'spam'],
            default => [],
        };
    }

    private function matchesAnyKeyword(string $text, array $keywords): bool
    {
        if ($text === '' || empty($keywords)) {
            return false;
        }

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && mb_strpos($text, mb_strtolower($keyword)) !== false) {
                return true;
            }
        }

        return false;
    }
}
