<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RequestList;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $currentSection = $request->query('section', 'request-lists');

        $query = RequestList::with(['items', 'offers.agent'])->where('user_id', $user->id);

        if ($requestSearch = $request->get('request_search')) {
            $query->where(function ($q) use ($requestSearch) {
                $q->where('title', 'like', "%{$requestSearch}%")
                  ->orWhere('note', 'like', "%{$requestSearch}%")
                  ->orWhere('status', 'like', "%{$requestSearch}%");
            });
        }

        $requestLists = $query->latest()->paginate(10);

        $favoriteAgentPostsQuery = AgentPost::with(['user', 'products'])
            ->whereIn('id', $user->favorites()
                ->where('favoriteable_type', AgentPost::class)
                ->pluck('favoriteable_id'));

        if ($favoriteSearch = trim((string) $request->query('favorite_search', ''))) {
            $favoriteAgentPostsQuery->where(function ($q) use ($favoriteSearch) {
                $q->where('title', 'like', "%{$favoriteSearch}%")
                    ->orWhere('description', 'like', "%{$favoriteSearch}%")
                    ->orWhere('country', 'like', "%{$favoriteSearch}%")
                    ->orWhereHas('user', function ($userQuery) use ($favoriteSearch) {
                        $userQuery->where('name', 'like', "%{$favoriteSearch}%");
                    });
            });
        }

        $favoriteAgentPosts = $favoriteAgentPostsQuery->latest()->paginate(9, ['*'], 'favorite_page');
        $favoriteAgentPostIds = $user->favorites()
            ->where('favoriteable_type', AgentPost::class)
            ->pluck('favoriteable_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $stats = [
            'ongoing_requests' => RequestList::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'offered', 'matched'])
                ->count(),
            'unread_messages' => 0,
            'favorite_posts' => count($favoriteAgentPostIds),
            'reviews_score' => '4.9 / 5',
        ];

        return view('dashboard', compact(
            'requestLists',
            'favoriteAgentPosts',
            'favoriteAgentPostIds',
            'currentSection',
            'stats'
        ));
    }
}