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
            $table->string('group_key')->nullable()->after('booking_truck_id');
        });
    }

    public function down(): void
    {
        Schema::table('expense_lines', function (Blueprint $table) {
            $table->dropColumn('group_key');
        });
    }
};
