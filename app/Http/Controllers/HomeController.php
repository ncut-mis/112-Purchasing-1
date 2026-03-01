<?php

namespace App\Http\Controllers;

use App\Models\AgentPost;
use App\Models\RequestList;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // 撈取最新的 6 筆代購貼文 (必須是開放狀態)
        $posts = AgentPost::with('user') // 預先載入 user 避免 N+1 問題
            ->where('status', 'open')
            ->latest()
            ->take(6)
            ->get();

        // 撈取最新的 8 筆請購清單
        $requests = RequestList::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(8)
            ->get();

        return view('home', compact('posts', 'requests'));
    }
}