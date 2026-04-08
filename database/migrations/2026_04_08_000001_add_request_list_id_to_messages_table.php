<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('request_list_id')
                ->nullable()
                ->after('id')
                ->constrained('request_lists')
                ->cascadeOnDelete();

            $table->index(['request_list_id', 'sender_id', 'receiver_id']);
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['request_list_id', 'sender_id', 'receiver_id']);
            $table->dropConstrainedForeignId('request_list_id');
        });
    }
};