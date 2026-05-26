<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('request_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('request_lists', 'expired_notice_removed_at')) {
                $table->timestamp('expired_notice_removed_at')->nullable()->after('expired_notice_read_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('request_lists', function (Blueprint $table) {
            if (Schema::hasColumn('request_lists', 'expired_notice_removed_at')) {
                $table->dropColumn('expired_notice_removed_at');
            }
        });
    }
};
