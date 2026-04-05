<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AgentApplication;
use App\Models\AgentPost;
use App\Models\RequestItem;
use App\Models\RequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $agentApplications = AgentApplication::with('user')->latest()->take(10)->get();
        $requestLists = RequestList::with(['items', 'user'])->latest()->take(10)->get();
        $posts = AgentPost::with(['user', 'products'])->latest()->take(10)->get();

        return view('admin.dashboard', compact('adminName', 'agentApplications', 'requestLists', 'posts'));
    }

    public function approveAgentApplication(AgentApplication $agentApplication)
    {
        if (! in_array($agentApplication->status, ['pending', 'resubmitted'])) {
            return redirect()->route('admin.dashboard')->with('status', '此申請已完成審核');
        }

        $agentApplication->update(['status' => 'approved']);

        return redirect()->route('admin.dashboard')->with('status', '已審核通過代購人申請');
    }

    public function rejectAgentApplication(AgentApplication $agentApplication)
    {
        if (! in_array($agentApplication->status, ['pending', 'resubmitted'])) {
            return redirect()->route('admin.dashboard')->with('status', '此申請已完成審核');
        }

        $agentApplication->update(['status' => 'rejected']);

        return redirect()->route('admin.dashboard')->with('status', '已審核不通過代購人申請');
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

    public function deleteAgentPost(AgentPost $agentPost)
    {
        $agentPost->load('products');

        DB::transaction(function () use ($agentPost) {
            foreach ($agentPost->products as $product) {
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $product->delete();
            }

            if ($agentPost->cover_image) {
                Storage::disk('public')->delete($agentPost->cover_image);
            }

            $agentPost->forceDelete();
        });

        return redirect()->route('admin.dashboard')->with('status', '代購貼文已刪除');
    }

    public function identityImage(AgentApplication $agentApplication, string $side)
    {
        if (! in_array($side, ['front', 'back'], true)) {
            abort(404);
        }

        $rawPath = $side === 'front'
            ? $agentApplication->id_image_front
            : $agentApplication->id_image_back;

        if (! $rawPath) {
            abort(404);
        }

        $normalized = ltrim($rawPath, '/');

        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = Str::after($normalized, 'storage/');
        }

        if (Str::startsWith($normalized, 'public/')) {
            $normalized = Str::after($normalized, 'public/');
        }

        if (! Storage::disk('public')->exists($normalized)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($normalized));
    }

    public function requestItemImage(RequestItem $requestItem)
    {
        $rawPath = $requestItem->reference_image;

        if (! $rawPath) {
            abort(404);
        }

        $normalized = ltrim($rawPath, '/');

        if (Str::startsWith($normalized, 'storage/')) {
            $normalized = Str::after($normalized, 'storage/');
        }

        if (Str::startsWith($normalized, 'public/')) {
            $normalized = Str::after($normalized, 'public/');
        }

        if (! Storage::disk('public')->exists($normalized)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($normalized));
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['admin_auth_id', 'admin_auth_name']);

        return redirect()->route('admin.login');
    }
}