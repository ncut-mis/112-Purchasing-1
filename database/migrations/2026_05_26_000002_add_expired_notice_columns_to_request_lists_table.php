<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('request_lists', 'expired_notified_at')) {
                $table->timestamp('expired_notified_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('request_lists', 'expired_notice_read_at')) {
                $table->timestamp('expired_notice_read_at')->nullable()->after('expired_notified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('request_lists', function (Blueprint $table) {
            if (Schema::hasColumn('request_lists', 'expired_notice_read_at')) {
                $table->dropColumn('expired_notice_read_at');
            }
            if (Schema::hasColumn('request_lists', 'expired_notified_at')) {
                $table->dropColumn('expired_notified_at');
            }
        });
    }
};
