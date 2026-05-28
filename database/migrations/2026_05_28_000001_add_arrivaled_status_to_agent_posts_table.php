<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE agent_posts MODIFY COLUMN status ENUM('draft', 'open', 'closed', 'completed', 'shipped', 'arrivaled') NOT NULL DEFAULT 'open'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE agent_posts MODIFY COLUMN status ENUM('draft', 'open', 'closed', 'completed', 'shipped') NOT NULL DEFAULT 'open'");
    }
};