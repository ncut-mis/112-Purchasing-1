<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
{
    $this->call([
        // 確保這裡叫 RandomPostSeeder 而不是 PostSeeder[cite: 2, 3]
        RandomPostSeeder::class,
        AgentApplicationSeeder::class,
        RequestListSeeder::class,
        people::class,
        AgentPostFavoriteSeeder::class,
        AgentPostOrderSeeder::class,
    ]);
}
}