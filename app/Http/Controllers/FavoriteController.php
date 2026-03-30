<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use App\Models\Favorite;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    // 收藏或取消收藏請購清單
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:request_list,agent_post'],
            'id' => ['required', 'integer'],
        ]);

        $user = Auth::user();

        if ($validated['type'] === 'request_list') {
            $requestList = RequestList::query()->findOrFail($validated['id']);
            if ((int) $requestList->user_id === (int) $user->id) {
                return response()->json(['message' => '不能收藏自己的請購清單'], 422);
            }

            $favoriteableType = RequestList::class;
            $favoriteableId = $requestList->id;
        }

        if ($validated['type'] === 'agent_post') {
            $agentPost = AgentPost::query()->findOrFail($validated['id']);
            if ((int) $agentPost->user_id === (int) $user->id) {
                return response()->json(['message' => '不能收藏自己的代購貼文'], 422);
            }

            $favoriteableType = AgentPost::class;
            $favoriteableId = $agentPost->id;
        }

        $favorite = $user->favorites()
            ->where('favoriteable_type', $favoriteableType)
            ->where('favoriteable_id', $favoriteableId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $status = 'removed';
        } else {
            $user->favorites()->create([
                'favoriteable_type' => $favoriteableType,
                'favoriteable_id' => $favoriteableId,
            ]);
            $status = 'added';
        }

        return response()->json([
            'status' => $status,
            'type' => $validated['type'],
            'id' => $favoriteableId,
        ]);
    }
}