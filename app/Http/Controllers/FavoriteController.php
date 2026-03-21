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

        [$favoriteableType, $favoriteableId] = match ($validated['type']) {
            'request_list' => [RequestList::class, RequestList::query()->findOrFail($validated['id'])->id],
            'agent_post' => [AgentPost::class, AgentPost::query()->findOrFail($validated['id'])->id],
        };

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