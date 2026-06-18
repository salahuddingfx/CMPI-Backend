<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('role');
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->string('image')->nullable()->after('read_time');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('image')->nullable()->after('venue');
        });

        Schema::table('notices', function (Blueprint $table) {
            $table->string('image')->nullable()->after('file_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn('image');
        });
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('image');
        });
        Schema::table('notices', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
