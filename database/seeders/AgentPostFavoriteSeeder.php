<?php

namespace Database\Seeders;

use App\Models\AgentPost;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Seeder;

class AgentPostFavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $posts = AgentPost::query()->where('status', 'open')->get();
        $users = User::query()->get();

        if ($posts->isEmpty() || $users->isEmpty()) {
            $this->command?->warn('略過 AgentPostFavoriteSeeder：缺少使用者或代購團資料。');
            return;
        }

        $popularPostIds = $posts
            ->shuffle()
            ->take(max(1, (int) ceil($posts->count() * 0.3)))
            ->pluck('id')
            ->all();

        foreach ($users as $user) {
            $candidatePosts = $posts->where('user_id', '!=', $user->id)->values();
            if ($candidatePosts->isEmpty()) {
                continue;
            }

            foreach ($candidatePosts as $post) {
                $isPopular = in_array($post->id, $popularPostIds, true);
                $probability = $isPopular ? 65 : 18;

                if (random_int(1, 100) > $probability) {
                    continue;
                }

                Favorite::firstOrCreate([
                    'user_id' => $user->id,
                    'favoriteable_id' => $post->id,
                    'favoriteable_type' => AgentPost::class,
                ]);
            }
        }

        $this->command?->info('已完成 AgentPost 收藏比例模擬（含熱門貼文高收藏權重）。');
    }
}
