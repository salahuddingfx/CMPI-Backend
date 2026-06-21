<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->string('description')->nullable()->after('title');
            $table->string('type')->default('tuition')->after('description');
            $table->string('academic_year')->nullable()->after('type');
            $table->timestamp('paid_at')->nullable()->after('due');
            $table->string('payment_method')->nullable()->after('paid_at');
            $table->string('transaction_id')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['description', 'type', 'academic_year', 'paid_at', 'payment_method', 'transaction_id']);
        });
    }
};
