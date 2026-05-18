<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 移除 'paid','purchasing'，加入 'wait-for-ship','arrivaled'
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending_payment','wait-for-ship','shipped','completed','cancelled','refunded','arrivaled') DEFAULT 'pending_payment'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 還原成原本的 enum（包含 paid, purchasing）
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending_payment','paid','purchasing','shipped','completed','cancelled','refunded') DEFAULT 'pending_payment'");
    }
};
