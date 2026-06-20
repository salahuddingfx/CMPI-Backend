<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institute_results', function (Blueprint $table) {
            $table->id();
            $table->string('roll')->index();
            $table->string('semester');
            $table->string('academic_year');
            $table->string('status'); // Passed or Referred
            $table->json('referred_subjects')->nullable();
            $table->text('raw_text')->nullable();
            $table->timestamps();

            $table->unique(['roll', 'semester', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institute_results');
    }
};
