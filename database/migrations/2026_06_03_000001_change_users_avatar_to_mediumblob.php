<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'avatar')) {
            DB::statement('ALTER TABLE `users` MODIFY `avatar` MEDIUMBLOB NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'avatar')) {
            DB::statement('ALTER TABLE `users` MODIFY `avatar` VARCHAR(255) NULL');
        }
    }
};
