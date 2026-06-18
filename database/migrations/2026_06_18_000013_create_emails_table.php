<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('from_email');
            $table->string('to_email');
            $table->string('cc')->nullable();
            $table->string('subject');
            $table->string('preview');
            $table->longText('body');
            $table->date('date');
            $table->enum('folder', ['inbox','sent','drafts','trash','archive','spam']);
            $table->boolean('read')->default(false);
            $table->boolean('starred')->default(false);
            $table->enum('label', ['work','personal','urgent'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};