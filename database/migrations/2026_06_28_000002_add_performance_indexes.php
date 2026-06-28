<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bteb_results', function (Blueprint $table) {
            // Composite index for the most common search: roll + semester
            $table->index(['roll', 'semester'], 'bteb_roll_semester_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('email', 'users_email_idx');
            $table->index('student_id', 'users_student_id_idx');
        });

        Schema::table('admissions', function (Blueprint $table) {
            $table->index('application_id', 'admissions_app_id_idx');
        });

        Schema::table('notices', function (Blueprint $table) {
            $table->index('date', 'notices_date_idx');
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->index('date', 'blogs_date_idx');
            $table->index('slug', 'blogs_slug_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bteb_results', function (Blueprint $table) {
            $table->dropIndex('bteb_roll_semester_idx');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_idx');
            $table->dropIndex('users_student_id_idx');
        });
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropIndex('admissions_app_id_idx');
        });
        Schema::table('notices', function (Blueprint $table) {
            $table->dropIndex('notices_date_idx');
        });
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropIndex('blogs_date_idx');
            $table->dropIndex('blogs_slug_idx');
        });
    }
};
