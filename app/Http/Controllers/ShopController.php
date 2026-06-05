<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AgentPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    /**
     * 顯示前台首頁大廳 (支援貼文搜尋)
     */
    public function home(Request $request)
    {
        $query = AgentPost::where('status', 'open')->with(['user', 'products']);

        // 1. 這裡處理精準 ID 搜尋 (由追蹤名單跳轉過來時)
        if ($postId = $request->input('post_id')) {
            $query->where('id', $postId);
        } 
        // 2. 這裡處理關鍵字搜尋
        elseif ($search = $request->input('search')) {
            $searchTerm = "%{$search}%";
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }

        $posts = $query->latest()->paginate(12)->withQueryString();

        return view('home', compact('posts'));
    }

    /**
     * 【核心優化】：顯示「找代購」大廳頁面
     * 僅撈取在 agent_applications 表中審核狀態為 'approved' 的代購人
     */
    public function store(Request $request)
    {
        // 1. 初始化查詢：透過 whereHas 強制約束，只撈取有代購申請且狀態為 'approved' 的使用者
        $query = User::whereHas('agentApplication', function($q) {
            $q->where('status', 'approved');
        })->with([
            'agentApplication',
            'agentPosts' => function ($postQuery) {
                $postQuery->where('status', 'open')->latest();
            },
        ]); // 預載入關聯，且彈窗只顯示接單中的代購團

        // 2. 【核心修正】：直接在資料庫查詢層過濾掉目前登入的代購人自己
        // 這樣可以確保後端傳出去的資料筆數與前端統計人數 100% 同步，且不影響分頁
        if (Auth::check()) {
            $query->where('id', '!=', Auth::id());
        }

          // 3. 先保存國家篩選條件，等資料取出後再解析 JSON 內容精準比對
        $country = $request->input('country');

        // 4. 處理代購人名稱或個人簡介的關鍵字搜尋
        if ($search = $request->input('search')) {
            $searchTerm = "%{$search}%";
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('bio', 'like', $searchTerm);
            });
        }

        // 5. 撈取符合條件的所有代購人（交由前端 Blade 搭配 Alpine.js 進行每頁 12 筆的滾動加載）
        $agents = $query->get();

        if ($country) {
            $agents = $agents->filter(function (User $agent) use ($country) {
                return $this->agentServesCountry($agent, $country);
            })->values();
        }

        // 6. 將過濾後的代購人集合傳遞給前台 views/shop/store.blade.php 渲染
        return view('shop.store', compact('agents'));
    }

    /**
     * 判斷代購人是否支援指定國家。
     */
    private function agentServesCountry(User $agent, string $country): bool
    {
        $countries = $this->normalizePurchasableCountries($agent->purchasable_countries);

        foreach ($this->countrySearchTerms($country) as $searchCountry) {
            if (in_array($searchCountry, $countries, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 將舊版篩選值與目前實際儲存的國家名稱互相對應。
     */
    private function countrySearchTerms(string $country): array
    {
        return match ($country) {
            '歐洲' => ['歐洲', '英國'],
            '英國' => ['英國', '歐洲'],
            default => [$country],
        };
    }

    /**
     * 將可代購國家欄位正規化成陣列。
     *
     * 舊資料可能是 JSON 字串、被重複 JSON 編碼的字串，或已經被 Eloquent cast 成陣列。
     */
    private function normalizePurchasableCountries(mixed $countriesData): array
    {
        while (is_string($countriesData)) {
            $decoded = json_decode($countriesData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }

            $countriesData = $decoded;
        }

        if (! is_array($countriesData)) {
            return [];
        }

        return array_values(array_filter($countriesData, fn ($country) => is_string($country) && $country !== ''));
    }
}