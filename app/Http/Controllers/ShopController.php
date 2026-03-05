<?php

namespace App\Http\Controllers;

use App\Models\Post;  // ← 改成 Post（第6行）
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function store(Request $request)
    {
        $query = Post::query();  // ← 改成 Post（第12行）

        // 搜尋貼文標題或內容
        if ($search = $request->search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")   // ← title，不是 name
                  ->orWhere('content', 'like', "%{$search}%");  // ← 加搜內容
            });
        }

        $posts = $query->paginate(12);  // ← 改成 $posts

        return view('shop.store', compact('posts'));  // ← 改成 'posts'
    }
}
