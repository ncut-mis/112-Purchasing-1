<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('request_lists', 'quote_notice_seen_count')) {
                $table->unsignedInteger('quote_notice_seen_count')->default(0)->after('expired_notice_removed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('request_lists', function (Blueprint $table) {
            if (Schema::hasColumn('request_lists', 'quote_notice_seen_count')) {
                $table->dropColumn('quote_notice_seen_count');
            }
        });
    }
};

