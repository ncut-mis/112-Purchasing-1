<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('quotes', function (Blueprint $table) {
        $table->id();
        // 1. 關連到需求單 (request_lists 表)
        $table->foreignId('request_list_id')->constrained()->onDelete('cascade'); 
        
        // 2. 報價的代購人 (users 表)
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
        
        // 3. 報價總金額
        $table->decimal('price', 10, 2); 
        
        // 4. 代購時段或備註 (對應你 JS 的 availableTime)
        $table->text('comment')->nullable(); 
        
        // 5. 狀態
        $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
