<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AgentApplication;
use App\Models\Post;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
        $agentApplications = AgentApplication::latest()->take(10)->get();
        $requestLists = RequestList::with('items')->latest()->take(10)->get();
        $posts = Post::latest()->take(10)->get();

        return view('admin.dashboard', compact('adminName', 'agentApplications', 'requestLists', 'posts'));
    }


    public function deleteRequestList(RequestList $requestList)
    {
        $requestList->load('items');

        foreach ($requestList->items as $item) {
            if ($item->reference_image) {
                Storage::disk('public')->delete($item->reference_image);
            }
        }

        $requestList->forceDelete();

        return redirect()->route('admin.dashboard')->with('status', '請購清單已刪除');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['admin_auth_id', 'admin_auth_name']);

        return redirect()->route('admin.login');
    }
}
