<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'returned', 'shipped', 'arrivaled', 'completed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected', 'returned', 'shipped', 'completed') NOT NULL DEFAULT 'pending'");
    }
};