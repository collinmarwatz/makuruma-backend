<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expense_orders', function (Blueprint $table) {
            $table->string('payment_account')->nullable()->after('total_amount');
            $table->string('initiated_by')->nullable()->after('payment_account');
            $table->date('payment_date')->nullable()->after('initiated_by');
        });
    }

    public function down(): void
    {
        Schema::table('expense_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_account', 'initiated_by', 'payment_date']);
        });
    }
};
