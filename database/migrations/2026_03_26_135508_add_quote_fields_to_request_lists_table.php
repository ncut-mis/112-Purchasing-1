<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('request_lists', function (Blueprint $table) {
            $table->json('agent_quotes')->nullable()->after('budget_total');     // 報價陣列
            $table->decimal('agent_quote_total', 10, 2)->nullable()->after('agent_quotes');  // 最新總報價
            $table->timestamp('latest_quote_at')->nullable()->after('agent_quote_total');    // 最新報價時間
        });
    }

    public function down()
    {
        Schema::table('request_lists', function (Blueprint $table) {
            $table->dropColumn(['agent_quotes', 'agent_quote_total', 'latest_quote_at']);
        });
    }
};
