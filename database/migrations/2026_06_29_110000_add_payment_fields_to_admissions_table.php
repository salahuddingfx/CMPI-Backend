<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('blood_group');
            $table->string('txn_id')->nullable()->after('payment_method');
            $table->enum('payment_status', ['unpaid', 'paid', 'pending_verification'])->default('pending_verification')->after('txn_id');
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'txn_id', 'payment_status']);
        });
    }
};
