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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Example: "Private Sessions", "Small Group"
            $table->enum('type', ['private', 'group'])->default('private');
            $table->integer('min_students')->default(1); // 1, 2, 6
            $table->integer('max_students')->default(1); // 1, 5, 10
            $table->decimal('price', 8, 2);
            $table->integer('sessions_count')->default(1); // عدد الحصص المتاحة في الباقة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
