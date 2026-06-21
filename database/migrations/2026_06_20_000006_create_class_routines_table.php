<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_routines', function (Blueprint $table) {
            $table->id();
            $table->string('department'); // CST, Civil, Electrical
            $table->string('semester'); // 1st, 2nd, etc. or "all"
            $table->string('academic_year'); // 2025-26
            $table->string('title'); // "Spring 2026 Class Routine"
            $table->string('pdf_path'); // stored PDF file path
            $table->string('original_name'); // original filename
            $table->timestamps();

            $table->unique(['department', 'semester', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_routines');
    }
};
