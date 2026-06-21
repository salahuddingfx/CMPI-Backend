<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->integer('technology_code')->after('department')->nullable();
            $table->string('technology_name')->after('technology_code')->nullable();
            $table->integer('theory_marks')->after('credit')->default(0);
            $table->integer('practical_marks')->after('theory_marks')->default(0);
            $table->integer('total_marks')->after('practical_marks')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn([
                'technology_code',
                'technology_name',
                'theory_marks',
                'practical_marks',
                'total_marks',
            ]);
        });
    }
};
