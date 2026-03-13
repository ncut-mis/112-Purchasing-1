<?php

namespace App\Http\Controllers;

use App\Models\RequestList;
use Illuminate\Http\Request;

class AgentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = RequestList::with(['items', 'user'])
            ->where('status', 'pending')
            ->latest();

        if ($keyword = trim((string) $request->query('q', ''))) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('note', 'like', "%{$keyword}%")
                    ->orWhereHas('items', function ($itemQuery) use ($keyword) {
                        $itemQuery->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($country = $request->query('country')) {
            $query->where('country', $country);
        }

        $requestLists = $query->paginate(10)->withQueryString();

        return view('agent.dashboard', [
            'requestLists' => $requestLists,
            'selectedCountry' => $country ?? 'all',
            'keyword' => $keyword ?? '',
        ]);
    }
}
