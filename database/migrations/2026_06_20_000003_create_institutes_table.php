<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name');
            $table->string('tagline')->nullable();
            $table->text('address');
            $table->string('phone');
            $table->string('email');
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('eiin')->nullable();
            $table->string('established')->nullable();
            $table->timestamps();
        });

        DB::table('institutes')->insert([
            'name' => "Cox's Bazar Model Polytechnic Institute",
            'short_name' => 'CMPI',
            'tagline' => 'Excellence in Technical Education',
            'address' => 'College Road, Cox\'s Bazar 4750, Bangladesh',
            'phone' => '+880 341 000000',
            'email' => 'info@cmpi.edu.bd',
            'website' => 'https://www.cmpi.edu.bd',
            'eiin' => '134567',
            'established' => '2008',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('institutes');
    }
};
