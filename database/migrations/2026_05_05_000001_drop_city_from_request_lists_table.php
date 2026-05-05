<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('request_lists', 'city')) {
            Schema::table('request_lists', function (Blueprint $table) {
                $table->dropColumn('city');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('request_lists', 'city')) {
            Schema::table('request_lists', function (Blueprint $table) {
                $table->string('city')->nullable()->after('country');
            });
        }
    }
};
