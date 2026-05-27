<?php
 
namespace App\Http\Controllers;
 
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 
class RequestListChatController extends Controller
{
    private function ensureChatEnabledStatus(RequestList $requestList): void
    {
        $enabledStatuses = ['offered', 'matched', 'wait-for-ship', 'shipped', 'arrivaled'];
        if (!in_array((string) $requestList->status, $enabledStatuses, true)) {
            abort(403, '目前此請購單狀態不可使用聊天功能。');
        }
    }

    private function resolveEligibleAgentIds(RequestList $requestList): array
    {
        $agentIds = [];
        if (!empty($requestList->people)) {
            $agentIds[] = (int) $requestList->people;
        }
        $quoteAgentIds = $requestList->quotes()
            ->orderByDesc('created_at')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        return array_values(array_unique(array_merge($agentIds, $quoteAgentIds)));
    }

    private function resolveAgentId(RequestList $requestList): ?int
    {
        return $this->resolveEligibleAgentIds($requestList)[0] ?? null;
    }

    // 顯示聊天頁面
    public function show(RequestList $requestList)
    {
        $user = Auth::user();
        $this->ensureChatEnabledStatus($requestList);
        $agentId = $this->resolveAgentId($requestList);

        $isBuyer = (int) $requestList->user_id === $user->id;
        $eligibleAgentIds = $this->resolveEligibleAgentIds($requestList);
        $isAgent = in_array((int) $user->id, $eligibleAgentIds, true);

        if (!$isBuyer && !$isAgent) {
            abort(403);
        }

        return view('request_lists.chat', compact('requestList', 'isBuyer', 'agentId', 'eligibleAgentIds'));
    }

    // 取得歷史訊息（點開對話框時觸發）
    public function history(RequestList $requestList, Request $request)
    {
        $user = Auth::user();
        $agentId = $this->resolveAgentId($requestList);

        $isBuyer = (int) $requestList->user_id === $user->id;
        $eligibleAgentIds = $this->resolveEligibleAgentIds($requestList);
        $isAgent = in_array((int) $user->id, $eligibleAgentIds, true);

        if (!$isBuyer && !$isAgent) {
            abort(403);
        }

        $this->ensureChatEnabledStatus($requestList);
        $myId = $user->id;
        $chatPartnerId = null;

        if ($isBuyer) {
            $requestedAgentId = (int) $request->input('agent_id', 0);
            $chatPartnerId = in_array($requestedAgentId, $eligibleAgentIds, true)
                ? $requestedAgentId
                : $agentId;
        } else {
            $chatPartnerId = $requestList->user_id;
        }

        if (!$chatPartnerId) {
            return response()->json([]);
        }

        // 標記已讀（用 read_at 欄位）
        $updated = Message::where('request_list_id', $requestList->id)
            ->where('sender_id', $chatPartnerId)
            ->where('receiver_id', $myId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // 如果有更新，廣播已讀通知給對方
        if ($updated > 0) {
            broadcast(new \App\Events\MessageRead(
                $myId, $chatPartnerId, now()->format('H:i'), $requestList->id
            ))->toOthers();
        }

        // 撈取對話紀錄
        $messages = Message::where('request_list_id', $requestList->id)
            ->where(function ($q) use ($myId, $chatPartnerId) {
                $q->where('sender_id', $myId)->where('receiver_id', $chatPartnerId);
            })
            ->orWhere(function ($q) use ($myId, $chatPartnerId) {
                $q->where('sender_id', $chatPartnerId)->where('receiver_id', $myId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $formatted = $messages->map(function ($msg) use ($myId) {
            return [
                'id'      => $msg->id,
                'sender'  => ((int) $msg->sender_id === (int) $myId) ? 'me' : 'other',
                'name'    => $msg->sender->name ?? '',
                'text'    => $msg->body,
                'time'    => $msg->created_at->format('H:i'),
                'read_at' => $msg->read_at,
            ];
        });

        return response()->json($formatted);
    }

    // 標記已讀 API
    public function markRead(Request $request, RequestList $requestList)
    {
        $myId = Auth::id();

        $updated = Message::where('request_list_id', $requestList->id)
            ->where('receiver_id', $myId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($updated > 0) {
            $lastMsg = Message::where('request_list_id', $requestList->id)
                ->where('receiver_id', $myId)
                ->orderByDesc('created_at')
                ->first();
            if ($lastMsg) {
                broadcast(new \App\Events\MessageRead(
                    $myId, $lastMsg->sender_id, now()->format('H:i'), $requestList->id
                ))->toOthers();
            }
        }

        return response()->json(['ok' => true]);
    }

    // 發送訊息
    public function send(RequestList $requestList, Request $request)
    {
        $user = Auth::user();
        $this->ensureChatEnabledStatus($requestList);
        $agentId = $this->resolveAgentId($requestList);

        $isBuyer = (int) $requestList->user_id === $user->id;
        $eligibleAgentIds = $this->resolveEligibleAgentIds($requestList);
        $isAgent = in_array((int) $user->id, $eligibleAgentIds, true);

        if (!$isBuyer && !$isAgent) {
            abort(403);
        }

        $request->validate(['body' => 'required|string|max:1000']);

        $receiverId = null;
        if ($isBuyer) {
            $requestedReceiverId = (int) $request->input('receiver_id', 0);
            $receiverId = in_array($requestedReceiverId, $eligibleAgentIds, true)
                ? $requestedReceiverId
                : $agentId;
        } else {
            $receiverId = $requestList->user_id;
        }

        if (!$receiverId) {
            return response()->json(['message' => '目前尚未有可聊天的代購人。'], 422);
        }

        $message = Message::create([
            'request_list_id' => $requestList->id,
            'sender_id'       => $user->id,
            'receiver_id'     => $receiverId,
            'body'            => $request->body,
        ]);

        broadcast(new MessageSent(
            $user->name,
            $request->body,
            $user->id,
            $receiverId,
            $message->id,
            $message->created_at->format('H:i'),
            $requestList->id,
        ))->toOthers();

        return response()->json([
            'id'     => $message->id,
            'sender' => 'me',
            'name'   => $user->name,
            'text'   => $message->body,
            'time'   => $message->created_at->format('H:i'),
        ]);
    }
}