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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            // Delete materials if category is deleted
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); 
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['video', 'pdf', 'quiz'])->default('pdf');
            $table->string('attachment'); // URL or file path
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
