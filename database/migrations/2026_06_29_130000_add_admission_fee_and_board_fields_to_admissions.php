<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->decimal('admission_fee_amount', 10, 2)->default(5000.00)->after('payment_status');
            $table->enum('admission_fee_status', ['unpaid', 'paid', 'pending_verification'])->default('unpaid')->after('admission_fee_amount');
            $table->enum('board_confirmation', ['pending', 'confirmed'])->default('pending')->after('admission_fee_status');
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn(['admission_fee_amount', 'admission_fee_status', 'board_confirmation']);
        });
    }
};
