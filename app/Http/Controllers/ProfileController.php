<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage; // 處理檔案刪除
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * 更新代購人專屬個人資訊（將暱稱輸入直接儲存於 name 欄位並確保唯一性）
     */
    public function updateAgentProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        // 1. 【核心修正】：因為資料庫中只有 name 欄位，我們直接將前端傳來的 'nickname' 欄位，
        // 驗證並儲存到資料庫的 'name' 欄位中，同時確保在 users 表的 name 欄位中是唯一的（排除自己目前的 ID）
        $validated = $request->validate([
            'nickname' => [
                'required', 
                'string', 
                'max:255', 
                'unique:users,name,' . $user->id // 驗證 users 表的 name 欄位是否唯一，允許自己更新
            ],
            'bio' => ['nullable', 'string', 'max:1000'],
            'countries' => ['nullable', 'array'],
            'countries.*' => ['string'],
            'avatar' => ['nullable', 'image', 'max:2048'], // 限制圖片最大 2MB
        ]);

        // 2. 處理頭像檔案上傳
        if ($request->hasFile('avatar')) {
            if ($user->avatar && is_string($user->avatar) && strlen($user->avatar) <= 1024 && preg_match('/^[\x20-\x7E]+$/', $user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = file_get_contents($request->file('avatar')->getRealPath());
        }

        // 3. 【核心修正】：直接將驗證後的 nickname 寫入資料庫的 name 欄位
        $user->name = $validated['nickname'];
        $user->bio = $validated['bio'] ?? null;
        
        // 儲存可代購國家
        $user->purchasable_countries = $validated['countries'] ?? [];

        $user->save();

        return Redirect::route('agent.profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}