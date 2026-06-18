<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('semester');
            $table->decimal('sgpa', 3, 2);
            $table->json('courses');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_results');
    }
};