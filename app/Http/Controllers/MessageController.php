<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // 請購人聊天頁：只顯示「我以 buyer 身份發送」的對話對象
    public function index(Request $request)
    {
        $userId = Auth::id();

        $sentToIds = \DB::table('messages')
            ->where('sender_id', $userId)
            ->where('context', 'buyer')
            ->pluck('receiver_id')
            ->unique()
            ->values();

        $chatPartners = User::whereIn('id', $sentToIds)->get();

        // 如果從找代購頁面帶來 ?partner=ID，自動加入對話列表
        $autoOpenPartnerId = null;
        if ($request->filled('partner')) {
            $partner = User::find($request->partner);
            if ($partner && $partner->id !== $userId) {
                if (!$chatPartners->contains('id', $partner->id)) {
                    $chatPartners->push($partner);
                }
                $autoOpenPartnerId = $partner->id;
            }
        }

        return view('messages.index', compact('chatPartners', 'autoOpenPartnerId'));
    }

    // 代購人聊天頁：顯示「以 buyer 身份傳給我」或「我以 agent 身份回覆過」的對話對象
    public function agentIndex(Request $request)
    {
        $userId = Auth::id();

        // 別人以 buyer 身份傳給我
        $receivedFrom = \DB::table('messages')
            ->where('receiver_id', $userId)
            ->where('context', 'buyer')
            ->pluck('sender_id');

        // 我以 agent 身份回覆的對象
        $sentTo = \DB::table('messages')
            ->where('sender_id', $userId)
            ->where('context', 'agent')
            ->pluck('receiver_id');

        $partnerIds = $receivedFrom->merge($sentTo)
            ->unique()
            ->filter(fn($id) => $id != $userId)
            ->values();

        $chatPartners = User::whereIn('id', $partnerIds)->get();

        return view('agent.chat', compact('chatPartners'));
    }

    // 取得與某人的歷史訊息
    public function history(User $user)
    {
        $myId = Auth::id();

        $messages = Message::with('sender')
            ->where(function ($q) use ($myId, $user) {
                $q->where('sender_id', $myId)->where('receiver_id', $user->id);
            })
            ->orWhere(function ($q) use ($myId, $user) {
                $q->where('sender_id', $user->id)->where('receiver_id', $myId);
            })
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'        => $m->id,
                'sender'    => $m->sender_id === $myId ? 'me' : 'other',
                'name'      => $m->sender->name,
                'text'      => $m->body,
                'time'      => $m->created_at->format('H:i'),
                'read_at'   => $m->read_at,
            ]);

        // 標記已讀
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $myId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    // 傳送訊息並廣播（接收 context 參數）
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body'        => 'required|string|max:1000',
            'context'     => 'in:buyer,agent',
        ]);

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'body'        => $request->body,
            'context'     => $request->input('context', 'buyer'),
        ]);

        $message->load('sender');

        broadcast(new MessageSent(
            Auth::user()->name,
            $request->body,
            Auth::id(),
            $request->receiver_id,
            $message->id,
            $message->created_at->format('H:i'),
        ))->toOthers();

        return response()->json([
            'id'      => $message->id,
            'sender'  => 'me',
            'name'    => Auth::user()->name,
            'text'    => $message->body,
            'time'    => $message->created_at->format('H:i'),
        ]);
    }

    // 取得可以開啟新對話的用戶
    public function searchUsers(Request $request)
    {
        $keyword = $request->get('q', '');
        $myId = Auth::id();

        $users = User::where('id', '!=', $myId)
            ->where('name', 'like', "%{$keyword}%")
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($users);
    }

    // 取得未讀訊息數
    public function unreadCount()
    {
        $count = Message::where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }
}