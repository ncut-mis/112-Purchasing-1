<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE request_lists MODIFY COLUMN status ENUM('editing', 'pending', 'offered', 'matched', 'completed', 'cancelled') DEFAULT 'editing'");
    }

    public function down(): void
    {
        DB::statement("UPDATE request_lists SET status = 'pending' WHERE status = 'editing'");
        DB::statement("ALTER TABLE request_lists MODIFY COLUMN status ENUM('pending', 'offered', 'matched', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
