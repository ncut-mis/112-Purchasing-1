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
 
        // 定義這場對話的參與者
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

        // 💡 【修正問題二、三】當使用者點開聊天室讀取歷史訊息時，將對方傳給我的未讀訊息全部更新為「已讀」
        // 這會讓當下的「未讀」變「已讀」，同時因為資料庫變更，通知中心的紅點與計數也會自動扣除！
        Message::where('request_list_id', $requestList->id)
            ->where('sender_id', $chatPartnerId)
            ->where('receiver_id', $myId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
 
        // 撈取兩人的對話紀錄
        $messages = Message::where('request_list_id', $requestList->id)
            ->where(function ($q) use ($myId, $chatPartnerId) {
                $q->where('sender_id', $myId)->where('receiver_id', $chatPartnerId);
            })
            ->orWhere(function ($q) use ($myId, $chatPartnerId) {
                $q->where('sender_id', $chatPartnerId)->where('receiver_id', $myId);
            })
            ->orderBy('created_at', 'asc')
            ->get();
 
        // 格式化輸出
        $formatted = $messages->map(function ($msg) use ($myId) {
            return [
                'id' => $msg->id,
                'sender' => ((int) $msg->sender_id === (int) $myId) ? 'me' : 'other',
                'text' => $msg->body,
                'time' => $msg->created_at->format('H:i'),
            ];
        });
 
        return response()->json($formatted);
    }
 
    // 發送新訊息
    public function send(RequestList $requestList, Request $request)
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
            'is_read'         => false, // 新訊息預設為未讀
        ]);
 
        // 💡 【修正問題一】廣播給對方時，加上 ->toOthers()。
        // 這樣 Pusher 就不會把訊息再次推回給發送者本人，徹底解決重複兩則訊息的問題！
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
            'text'   => $message->body,
            'time'   => $message->created_at->format('H:i'),
        ]);
    }
}