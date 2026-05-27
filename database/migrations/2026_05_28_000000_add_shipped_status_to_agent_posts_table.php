<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 直接修改 ENUM，加入 shipped
        DB::statement("ALTER TABLE agent_posts MODIFY COLUMN status ENUM('draft', 'open', 'closed', 'completed', 'shipped') NOT NULL DEFAULT 'open'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE agent_posts MODIFY COLUMN status ENUM('draft', 'open', 'closed', 'completed') NOT NULL DEFAULT 'open'");
    }
};