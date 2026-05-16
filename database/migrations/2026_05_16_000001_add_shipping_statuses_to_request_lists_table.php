<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('request_lists') && Schema::hasColumn('request_lists', 'status')) {
            DB::statement("ALTER TABLE request_lists MODIFY COLUMN status ENUM('editing','pending','offered','matched','wait-for-ship','shipped','arrivaled','expired','completed','cancelled') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('request_lists') && Schema::hasColumn('request_lists', 'status')) {
            DB::statement("ALTER TABLE request_lists MODIFY COLUMN status ENUM('editing','pending','offered','matched','completed','cancelled') DEFAULT 'pending'");
        }
    }
};
