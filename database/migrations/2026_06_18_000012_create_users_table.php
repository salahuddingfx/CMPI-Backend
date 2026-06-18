<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('department')->nullable();
            $table->string('student_id')->nullable();
            $table->string('semester')->nullable();
            $table->string('session')->nullable();
            $table->string('phone')->nullable();
            $table->string('guardian')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('address')->nullable();
            $table->date('admission_date')->nullable();
            $table->string('role')->default('student');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};