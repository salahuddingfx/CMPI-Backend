<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_messages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g. chairman, management, principal
            $table->string('name');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('message');
            $table->string('avatar')->nullable();
            $table->timestamps();
        });

        DB::table('admin_messages')->insert([
            [
                'key' => 'chairman',
                'name' => 'Engr. Muhammad Shafi',
                'title' => 'Founder & Chairman',
                'subtitle' => 'Governing Council, CMPI',
                'message' => "Welcome to Cox's Bazar Model Polytechnic Institute. When we founded this institution, our goal was to break the geographical barrier and provide international-standard technical education right here in the coastal region.\n\nTechnology is changing rapidly, and traditional degrees alone are no longer enough. We focus on outcome-based education that links classroom lectures directly to industry needs. I wish our students a transformative learning experience.",
                'avatar' => 'https://api.dicebear.com/7.x/adventurer/svg?seed=chairman',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'management',
                'name' => 'Governing Council & Management',
                'title' => 'Board of Trustees & Directors',
                'subtitle' => "Cox's Bazar Model Polytechnic Institute",
                'message' => "The management board of CMPI is committed to ensuring full administrative support, state-of-the-art laboratory infrastructure, and collaborations with foreign tech institutes.\n\nWe are actively looking forward to expanding our campus space, creating internship placements across major technology and construction companies, and offering scholarships to outstanding performers. Our investment is in your future.",
                'avatar' => 'https://api.dicebear.com/7.x/adventurer/svg?seed=governing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'principal',
                'name' => 'Ln. Md. Didar Ullah',
                'title' => 'Principal & Visionary',
                'subtitle' => "Cox's Bazar Model Polytechnic Institute",
                'message' => "Dear Students and Stakeholders,\n\nCox's Bazar Model Polytechnic Institute has been a beacon of technical education in the Chittagong Hill Tracts region. Our mission is to produce skilled technologists who can contribute to the nation's development.\n\nWe offer three diploma programs — Computer Science & Technology, Civil Technology, and Electrical Technology — each designed with a blend of theoretical knowledge and practical skills.\n\nOur dedicated faculty, modern laboratories, and industry partnerships ensure our students are well-prepared for the challenges of the modern workforce. I encourage all students to make the most of the opportunities here.\n\nBest wishes for your academic journey.",
                'avatar' => '/principal.png',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_messages');
    }
};
