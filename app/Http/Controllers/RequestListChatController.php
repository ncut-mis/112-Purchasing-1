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

        abort_if($agentId < 1, 403, '此請購清單尚未有接單代購人，無法聊天。');

        $isParticipant = in_array($userId, [$ownerId, $agentId], true);
        abort_unless($isParticipant, 403);
    }
}