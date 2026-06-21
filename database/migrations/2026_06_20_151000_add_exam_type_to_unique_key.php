<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bteb_results', function ($table) {
            $table->dropUnique('bteb_results_roll_semester_regulation_unique');
            $table->unique(['roll', 'semester', 'regulation', 'exam_type'], 'bteb_results_roll_sem_reg_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('bteb_results', function ($table) {
            $table->dropUnique('bteb_results_roll_sem_reg_type_unique');
            $table->unique(['roll', 'semester', 'regulation'], 'bteb_results_roll_semester_regulation_unique');
        });
    }
};
