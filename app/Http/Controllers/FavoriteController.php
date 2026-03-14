<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    // 收藏或取消收藏請購清單
    public function toggle(Request $request)
    {
        $request->validate([
            'request_list_id' => 'required|integer|exists:request_lists,id',
        ]);

        $user = Auth::user();
        $requestListId = $request->input('request_list_id');
        $favorite = $user->favorites()
            ->where('favoriteable_type', 'App\\Models\\RequestList')
            ->where('favoriteable_id', $requestListId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $status = 'removed';
        } else {
            $user->favorites()->create([
                'favoriteable_type' => 'App\\Models\\RequestList',
                'favoriteable_id' => $requestListId,
            ]);
            $status = 'added';
        }

        return response()->json(['status' => $status]);
    }
}
