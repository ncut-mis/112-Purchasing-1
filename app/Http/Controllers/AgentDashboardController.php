<?php

namespace App\Http\Controllers;

use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        $selectedTime = $request->query('time', 'all');
        $today = Carbon::today();

        if ($selectedTime === 'urgent') {
            $query->whereDate('deadline', '>=', $today)
                ->whereDate('deadline', '<=', Carbon::now()->addDay());
        } elseif ($selectedTime === 'three_days') {
            $query->whereDate('deadline', '>=', $today)
                ->whereDate('deadline', '<=', Carbon::now()->addDays(3));
        } elseif ($selectedTime === 'this_week') {
            $query->whereDate('deadline', '>=', $today)
                ->whereDate('deadline', '<=', Carbon::now()->endOfWeek(Carbon::SUNDAY));
        }

        $requestLists = $query->paginate(10)->withQueryString();

        return view('agent.dashboard', [
            'requestLists' => $requestLists,
            'selectedCountry' => $country ?? 'all',
            'selectedTime' => $selectedTime,
            'keyword' => $keyword ?? '',
        ]);
    }
}
