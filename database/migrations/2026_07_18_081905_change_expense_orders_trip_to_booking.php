<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expense_orders', function (Blueprint $table) {
            $table->dropForeign(['trip_id']);
            $table->dropColumn('trip_id');
            $table->foreignId('booking_id')->nullable()->after('category')->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('expense_orders', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropColumn('booking_id');
            $table->foreignId('trip_id')->nullable()->constrained();
        });
    }
};