<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::guard('admin')->check() && ! $request->session()->has('admin_auth_id')) {
           return redirect()->route('login');
        }

        return $next($request);
    }
}
