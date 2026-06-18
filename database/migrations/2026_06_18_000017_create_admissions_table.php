<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->string('application_id')->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('department');
            $table->string('ssc_gpa');
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('address');
            $table->string('blood_group')->nullable();
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};