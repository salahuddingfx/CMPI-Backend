<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('category', ['general','academic','facility','hostel','other']);
            $table->text('description');
            $table->enum('status', ['pending','in-progress','resolved'])->default('pending');
            $table->integer('upvotes')->default(0);
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};