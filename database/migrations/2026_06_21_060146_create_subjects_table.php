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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('department'); // CST, CT, ET
            $table->string('semester'); // 1st Semester, 2nd Semester, etc.
            $table->string('subject_code'); // e.g. 28541
            $table->string('subject_name'); // e.g. Java Programming
            $table->integer('credit');
            $table->timestamps();

            $table->unique(['department', 'subject_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
