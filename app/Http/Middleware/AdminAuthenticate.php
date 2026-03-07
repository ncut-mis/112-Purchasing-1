<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->session()->has('admin_auth_id')) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}