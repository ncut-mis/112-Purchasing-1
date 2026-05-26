<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use App\Models\ContentReport;
use App\Models\RequestList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentReportController extends Controller
{
    public function store(Request $request): JsonResponse
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


        return response()->json([
            'status' => 'success',
            'message' => '檢舉已送出，管理員會盡快審核。',
        ]);
    }
}