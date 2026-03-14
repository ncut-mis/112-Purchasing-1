<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_lists', function (Blueprint $table) {
            // 1. 將 address_detail 欄位改名為 detail_address
            if (Schema::hasColumn('request_lists', 'address_detail')) {
                $table->renameColumn('address_detail', 'detail_address');
            }
        });

        // 2. 將舊 note 欄位內容搬到 detail_address，並清空 note
        \DB::statement('UPDATE request_lists SET detail_address = note WHERE note IS NOT NULL AND (detail_address IS NULL OR detail_address = "")');
        \DB::statement('UPDATE request_lists SET note = NULL');
    }

    public function down(): void
    {
        Schema::table('request_lists', function (Blueprint $table) {
            if (Schema::hasColumn('request_lists', 'detail_address')) {
                $table->renameColumn('detail_address', 'address_detail');
            }
        });
    }
};
