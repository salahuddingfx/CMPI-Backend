<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_jobs', function (Blueprint $table) {
            $table->id();
            $table->text('drive_url');
            $table->string('semester')->nullable()->default('auto');
            $table->string('regulation')->nullable()->default('auto');
            $table->string('holding_year')->nullable()->default('auto');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->integer('total_files')->default(0);
            $table->integer('processed_files')->default(0);
            $table->integer('total_results')->default(0);
            $table->json('error_log')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_jobs');
    }
};
