<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('session')->nullable()->after('department');
            $table->string('hsc_gpa')->nullable()->after('ssc_gpa');
            $table->json('documents')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn(['session', 'hsc_gpa', 'documents']);
        });
    }
};
