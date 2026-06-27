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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->nullable()->constrained()->onDelete('cascade'); 
            
            $table->enum('status', ['pending', 'active', 'rejected', 'expired'])->default('pending');
            
            $table->integer('remaining_sessions')->default(0); // Remaining Sessions
            $table->boolean('is_free_tier')->default(false); 

            $table->date('start_date')->nullable(); // Not Start untill admin approve 
            $table->date('expire_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
