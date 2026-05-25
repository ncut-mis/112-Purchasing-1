<?php
 
namespace App\Http\Controllers;
 
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 
class RequestListChatController extends Controller
{
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
        $agentId = $this->resolveAgentId($requestList);
 
        // 只有請託人或承接代購人才能進入
        $isBuyer = (int) $requestList->user_id === $user->id;
        $eligibleAgentIds = $this->resolveEligibleAgentIds($requestList);
        $isAgent = in_array((int) $user->id, $eligibleAgentIds, true);
 
        if (!$isBuyer && !$isAgent) {
            abort(403, '您沒有權限查看此聊天室。');
        }
 
        // 對方是誰
        $partner = $isBuyer
            ? $requestList->agent   // 我是請託人，對方是代購人
            : $requestList->user;   // 我是代購人，對方是請託人
 
        if (!$partner) {
            abort(404, '找不到聊天對象。');
        }
 
        // 載入歷史訊息
        $messages = Message::with('sender')
            ->where('request_list_id', $requestList->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'     => $m->id,
                'sender' => $m->sender_id === $user->id ? 'me' : 'other',
                'name'   => $m->sender->name,
                'text'   => $m->body,
                'time'   => $m->created_at->format('H:i'),
            ]);
 
        // 標記已讀
        Message::where('request_list_id', $requestList->id)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
 
        return view('messages.chat', compact('requestList', 'partner', 'messages', 'isBuyer'));
    }
 
    // 標記已讀（請託單聊天）
    public function markRead(Request $request, RequestList $requestList)
    {
        $user = Auth::user();
        $myId = $user->id;

        $updated = Message::where('request_list_id', $requestList->id)
            ->where('receiver_id', $myId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($updated > 0) {
            // 找出最後一筆訊息的發送者，廣播已讀
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

    // 傳送訊息
    public function send(Request $request, RequestList $requestList)
    {
        $user = Auth::user();
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
 
        // 廣播給對方
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