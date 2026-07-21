<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expense_orders', function (Blueprint $table) {
            $table->string('reference_no')->nullable()->unique()->after('order_number');
        });
    }

    public function down(): void
    {
        Schema::table('expense_orders', function (Blueprint $table) {
            $table->dropColumn('reference_no');
        });
    }
};