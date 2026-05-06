<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // 顯示聊天頁面，帶入歷史對話對象
    public function index(Request $request)
    {
        $userId = Auth::id();

        // 找出所有曾經和我聊過天的人
        $sentTo = \DB::table('messages')->where('sender_id', $userId)->pluck('receiver_id');
        $receivedFrom = \DB::table('messages')->where('receiver_id', $userId)->pluck('sender_id');
        $partnerIds = $sentTo->merge($receivedFrom)->unique()->values();
        $chatPartners = User::whereIn('id', $partnerIds)->get();

        // 如果從找代購頁面帶來 ?partner=ID，自動加入對話列表
        $autoOpenPartnerId = null;
        if ($request->filled('partner')) {
            $partner = User::find($request->partner);
            if ($partner && $partner->id !== $userId) {
                // 如果不在歷史對話列表裡，加進去
                if (!$chatPartners->contains('id', $partner->id)) {
                    $chatPartners->push($partner);
                }
                $autoOpenPartnerId = $partner->id;
            }
        }

        return view('messages.index', compact('chatPartners', 'autoOpenPartnerId'));
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

    // 傳送訊息並廣播
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body'        => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'body'        => $request->body,
        ]);

        $message->load('sender');

        // 廣播給接收者的私人頻道
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

    // 取得可以開啟新對話的用戶（排除已有對話的）
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