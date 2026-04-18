<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use App\Models\ContentReport;
use App\Models\RequestList;
use App\Services\ContentAutoReviewService;
use App\Services\ModerationModeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentReportController extends Controller
{
    public function store(
        Request $request,
        ModerationModeService $moderationModeService,
        ContentAutoReviewService $contentAutoReviewService
    ): JsonResponse
    {
        $validated = $request->validate([
            'target_type' => ['required', 'string', 'in:request_list,agent_post'],
            'target_id' => ['required', 'integer'],
            'report_type' => ['required', 'string', 'in:false_info,fraud,prohibited_items,copyright,harassment,spam,other'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user = $request->user();

        if ($validated['target_type'] === 'request_list') {
            $target = RequestList::findOrFail($validated['target_id']);
        } else {
            $target = AgentPost::findOrFail($validated['target_id']);
        }

        if ((int) $target->user_id === (int) $user->id) {
            return response()->json(['message' => '不能檢舉自己的內容'], 422);
        }

        $alreadyPending = ContentReport::query()
            ->where('reporter_id', $user->id)
            ->where('reportable_type', $target::class)
            ->where('reportable_id', $target->id)
            ->where('status', ContentReport::STATUS_PENDING)
            ->exists();

        if ($alreadyPending) {
            return response()->json(['message' => '你已檢舉過這則內容，請等待管理員審核'], 422);
        }

        $report = ContentReport::create([
            'reporter_id' => $user->id,
            'reportable_type' => $target::class,
            'reportable_id' => $target->id,
            'report_type' => $validated['report_type'],
            'reason' => trim($validated['reason']),
            'status' => ContentReport::STATUS_PENDING,
        ]);

        if ($moderationModeService->isAuto()) {
            $report->load('reportable');

            $isApproved = $contentAutoReviewService->shouldApprove($report);
            $report->update([
                'status' => $isApproved ? ContentReport::STATUS_APPROVED : ContentReport::STATUS_REJECTED,
                'reviewed_at' => now(),
                'review_note' => '系統自動審核（關鍵字與內容一致性比對）',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => $moderationModeService->isAuto()
                ? '檢舉已送出，系統已完成自動審核。'
                : '檢舉已送出，管理員會盡快審核。',
        ]);
    }
}