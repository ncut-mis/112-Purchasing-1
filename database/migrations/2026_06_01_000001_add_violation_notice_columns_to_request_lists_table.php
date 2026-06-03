<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_lists', function (Blueprint $table) {
            if (! Schema::hasColumn('request_lists', 'violation_notified_at')) {
                $table->timestamp('violation_notified_at')->nullable()->after('expired_notice_removed_at');
            }

            if (! Schema::hasColumn('request_lists', 'violation_notice_read_at')) {
                $table->timestamp('violation_notice_read_at')->nullable()->after('violation_notified_at');
            }

            if (! Schema::hasColumn('request_lists', 'violation_notice_removed_at')) {
                $table->timestamp('violation_notice_removed_at')->nullable()->after('violation_notice_read_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('request_lists', function (Blueprint $table) {
            if (Schema::hasColumn('request_lists', 'violation_notice_removed_at')) {
                $table->dropColumn('violation_notice_removed_at');
            }

            if (Schema::hasColumn('request_lists', 'violation_notice_read_at')) {
                $table->dropColumn('violation_notice_read_at');
            }

            if (Schema::hasColumn('request_lists', 'violation_notified_at')) {
                $table->dropColumn('violation_notified_at');
            }
        });
    }
};
