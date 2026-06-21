<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bteb_results', function (Blueprint $table) {
            $table->string('center_code')->nullable()->after('roll');
            $table->string('institute_name')->nullable()->after('center_code');
        });
    }

    public function down(): void
    {
        Schema::table('bteb_results', function (Blueprint $table) {
            $table->dropColumn(['center_code', 'institute_name']);
        });
    }
};
