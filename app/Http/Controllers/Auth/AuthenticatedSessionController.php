<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * 顯示登入頁面
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * 處理登入請求：包含一般使用者身分判定與管理員分流
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. 檢查是否觸發頻率限制 (Rate Limiting)
        $request->ensureIsNotRateLimited();

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

          // 2. 先嘗試以「管理員 (Admin)」身分登入 (優先比對 Email 欄位)
        if (Auth::guard('admin')->attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            // 清除頻率限制紀錄
            RateLimiter::clear($request->throttleKey());
            $request->session()->regenerate();

             $admin = Auth::guard('admin')->user();
            if ($admin) {
                // 與既有 admin middleware/session 相容
                $request->session()->put('admin_auth_id', $admin->id);
                $request->session()->put('admin_auth_name', $admin->name);
            }

          return redirect()->route('admin.dashboard');
        }

        // 3. Fallback：如果管理員在 Email 框輸入的是 Username (帳號名)
        if (Auth::guard('admin')->attempt(['username' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            RateLimiter::clear($request->throttleKey());
            $request->session()->regenerate();

            $admin = Auth::guard('admin')->user();
            if ($admin) {
                $request->session()->put('admin_auth_id', $admin->id);
                $request->session()->put('admin_auth_name', $admin->name);
            }

            return redirect()->route('admin.dashboard');
        }

       // 4. 嘗試以「一般使用者 (User)」身分登入 (使用 web guard)
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            RateLimiter::clear($request->throttleKey());
            $request->session()->regenerate();

             $user = Auth::guard('web')->user();

            // 【核心功能】：判斷該使用者是否為已通過審核的代購人
            // 檢查 user 關聯的 agentApplication 狀態
            if ($user && $user->agentApplication && $user->agentApplication->status === 'approved') {
                // 存入 Session 閃存標記 (僅限下一次頁面讀取)，供前端彈出角色選擇視窗
                $request->session()->flash('show_role_selector', true);
            }

            // 登入成功後跳轉至買家首頁
            return redirect()->route('home');
        }

        // 5. 登入失敗：增加失敗次數紀錄並拋出錯誤
        RateLimiter::hit($request->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * 處理登出請求：確保清除所有身分狀態
     */
    public function destroy(Request $request): RedirectResponse
    {
        // 同時清除兩個 Guard 的登入狀態
        Auth::guard('web')->logout();
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}