<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_calendars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('event_date')->index();
            $table->date('end_date')->nullable();
            $table->enum('category', ['exam', 'holiday', 'event', 'meeting', 'deadline', 'other'])
                  ->default('event')
                  ->index();
            $table->text('description')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_calendars');
    }
};
