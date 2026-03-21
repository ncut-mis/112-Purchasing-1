<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RequestList;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 只顯示當前登入用戶的清單
         $query = RequestList::with(['items', 'offers.agent'])->where('user_id', Auth::id());
        
        // 搜尋功能
        if ($request_search = $request->get('request_search')) {
            $query->where(function($q) use ($request_search) {
                $q->where('title', 'like', "%{$request_search}%")
                  ->orWhere('note', 'like', "%{$request_search}%")
                  ->orWhere('status', 'like', "%{$request_search}%");
            });
        }
        
        $requestLists = $query->latest()->paginate(10);
        
        return view('dashboard', compact('requestLists'));
    }
}