<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone');            // 聯絡電話
            $table->string('main_region');     // 主要代購地區 (如：日本、韓國)
            $table->text('experience');        // 代購經驗說明
            $table->string('status')->default('pending'); // 狀態：pending, approved, rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_applications');
    }
};