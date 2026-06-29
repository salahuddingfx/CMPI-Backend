<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->string('bkash_number')->nullable()->after('email');
            $table->string('nagad_number')->nullable()->after('bkash_number');
        });

        // Seed default values
        DB::table('institutes')->where('id', 1)->update([
            'bkash_number' => '+880 1888-000000',
            'nagad_number' => '+880 1888-111111',
        ]);
    }

    public function down(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn(['bkash_number', 'nagad_number']);
        });
    }
};
