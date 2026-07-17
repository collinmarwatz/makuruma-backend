<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->decimal('buying_price', 14, 2)->nullable()->after('capacity');
            $table->enum('trip_status', ['go', 'return', 'off_duty'])->default('off_duty')->after('status');
        });

        DB::statement("ALTER TABLE trucks MODIFY current_status ENUM('pending','loading','in_transit','at_border','offloading','delayed','breakdown','completed') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->dropColumn(['buying_price', 'trip_status']);
        });

        DB::statement("ALTER TABLE trucks MODIFY current_status ENUM('loading','in_transit','at_border','offloading','delayed','breakdown','completed') DEFAULT 'loading'");
    }
};