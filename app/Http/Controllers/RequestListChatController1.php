<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\RequestList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestListChatController extends Controller
{
    public function show(Request $request, RequestList $requestList): View
    {
        $userId = (int) $request->user()->id;
        $this->authorizeChat($requestList, $userId);

        $messages = Message::where('request_list_id', $requestList->id)
            ->where(function ($query) use ($requestList) {
                $query->where(function ($inner) use ($requestList) {
                    $inner->where('sender_id', $requestList->user_id)
                        ->where('receiver_id', $requestList->people);
                })->orWhere(function ($inner) use ($requestList) {
                    $inner->where('sender_id', $requestList->people)
                        ->where('receiver_id', $requestList->user_id);
                });
            })
            ->with(['sender:id,name'])
            ->orderBy('created_at')
            ->get();

        return view('request-list.chat', [
            'requestList' => $requestList,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, RequestList $requestList): JsonResponse|RedirectResponse
    {
        $userId = (int) $request->user()->id;
        $this->authorizeChat($requestList, $userId);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $receiverId = $userId === (int) $requestList->user_id
            ? (int) $requestList->people
            : (int) $requestList->user_id;

        $message = Message::create([
            'request_list_id' => $requestList->id,
            'sender_id' => $userId,
            'receiver_id' => $receiverId,
            'body' => trim($validated['body']),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $request->user()->name,
                    'body' => $message->body,
                    'created_at' => optional($message->created_at)->format('Y-m-d H:i'),
                ],
            ]);
        }

        return redirect()->route('request-list.chat.show', $requestList);
    }

    private function authorizeChat(RequestList $requestList, int $userId): void
    {
        $ownerId = (int) $requestList->user_id;
        $agentId = (int) ($requestList->people ?? 0);

        abort_if($agentId < 1, 403, '此請託單尚未有接單代購人，無法聊天。');

        $isParticipant = in_array($userId, [$ownerId, $agentId], true);
        abort_unless($isParticipant, 403);
    }
        public function reject($id)
        {
            $request = RequestList::findOrFail($id);

            // 1. 修正欄位名稱：將 agent_id 移除，因為你的表中沒有這個欄位
            $request->status = 'pending';
            $request->people = null;  // 這是你用來存放承接人 ID 的欄位
            $request->time = null;    // 清除時間訊息
            $request->agent_quote_total = 0; // 重置金額

            // 2. 儲存變更
            $request->save();

            // 3. 同步清除商品明細的單價
            if ($request->items) {
                $request->items()->update(['expected_price' => null]);
            }

            return back()->with('success', '已拒絕報價，單據已恢復徵求狀態。');
        }
}