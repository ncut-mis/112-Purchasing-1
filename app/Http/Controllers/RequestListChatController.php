<?php
 
namespace App\Http\Controllers;
 
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 
class RequestListChatController extends Controller
{
    // 顯示聊天頁面
    public function show(RequestList $requestList)
    {
        $user = Auth::user();
 
        // 只有請託人或承接代購人才能進入
        $isBuyer = (int) $requestList->user_id === $user->id;
        $isAgent = (int) $requestList->people  === $user->id;
 
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
 
    // 傳送訊息
    public function send(Request $request, RequestList $requestList)
    {
        $user = Auth::user();
 
        $isBuyer = (int) $requestList->user_id === $user->id;
        $isAgent = (int) $requestList->people  === $user->id;
 
        if (!$isBuyer && !$isAgent) {
            abort(403);
        }
 
        $request->validate(['body' => 'required|string|max:1000']);
 
        $receiverId = $isBuyer
            ? $requestList->people      // 請託人傳給代購人
            : $requestList->user_id;    // 代購人傳給請託人
 
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