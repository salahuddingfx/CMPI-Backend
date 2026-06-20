<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bteb_results', function (Blueprint $table) {
            $table->id();
            $table->string('roll')->index();
            $table->string('department');
            $table->string('semester');
            $table->string('regulation');
            $table->string('holding_year');
            $table->decimal('gpa', 3, 2)->nullable();
            $table->string('status'); // Passed or Referred
            $table->json('referred_subjects')->nullable();
            $table->text('raw_text')->nullable();
            $table->timestamps();

            // Prevent duplicates for same roll, semester and regulation
            $table->unique(['roll', 'semester', 'regulation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bteb_results');
    }
};
