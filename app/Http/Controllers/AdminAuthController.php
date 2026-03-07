<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $admin = Admin::where('username', $validated['username'])->first();

        if (! $admin || ! Hash::check($validated['password'], $admin->password)) {
            return back()->withErrors([
                'username' => '帳號或密碼錯誤',
            ])->withInput();
        }

        $request->session()->put('admin_auth_id', $admin->id);
        $request->session()->put('admin_auth_name', $admin->name);

        return redirect()->route('admin.dashboard');
    }

    public function dashboard(Request $request)
    {
        $adminName = $request->session()->get('admin_auth_name');

        return view('admin.dashboard', compact('adminName'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['admin_auth_id', 'admin_auth_name']);

        return redirect()->route('admin.login');
    }
}