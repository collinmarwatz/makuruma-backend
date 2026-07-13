<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('booking_trucks', function (Blueprint $table) {
        $table->string('current_location')->nullable()->after('offloading_date');
        $table->enum('current_status', ['loading', 'in_transit', 'at_border', 'offloading', 'delayed', 'completed'])
            ->default('loading')->after('current_location');
    });
}

public function down(): void
{
    Schema::table('booking_trucks', function (Blueprint $table) {
        $table->dropColumn(['current_location', 'current_status']);
    });
}
};
