<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gallery_album_id')->index();
            $table->string('url');
            $table->string('caption')->nullable();
            $table->timestamps();
        });

        Schema::table('gallery_albums', function (Blueprint $table) {
            $table->string('cover')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');

        Schema::table('gallery_albums', function (Blueprint $table) {
            $table->dropColumn('cover');
        });
    }
};
