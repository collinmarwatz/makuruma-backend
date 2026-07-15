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
        Schema::table('expense_lines', function (Blueprint $table) {
            $table->foreignId('booking_truck_id')->nullable()->after('vendor_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expense_lines', function (Blueprint $table) {
            $table->dropForeign(['booking_truck_id']);
            $table->dropColumn('booking_truck_id');
        });
    }
};
