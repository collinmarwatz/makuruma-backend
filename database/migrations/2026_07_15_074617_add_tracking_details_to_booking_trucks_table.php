<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('booking_trucks', function (Blueprint $table) {
            $table->date('actual_loading_date')->nullable()->after('capacity_override');
            $table->date('actual_offloading_date')->nullable()->after('actual_loading_date');
        });

        DB::statement("ALTER TABLE trucks MODIFY current_status ENUM('loading','in_transit','at_border','offloading','delayed','breakdown','completed') DEFAULT 'loading'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_trucks', function (Blueprint $table) {
            $table->dropColumn(['actual_loading_date', 'actual_offloading_date']);
        });

        DB::statement("ALTER TABLE trucks MODIFY current_status ENUM('loading','in_transit','at_border','offloading','delayed','completed') DEFAULT 'loading'");
    }
};