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
        $table->dropColumn(['loading_point', 'loading_point_arrival_date', 'offloading_point', 'offloading_date']);
    });
}

public function down(): void
{
    Schema::table('booking_trucks', function (Blueprint $table) {
        $table->string('loading_point')->nullable();
        $table->date('loading_point_arrival_date')->nullable();
        $table->string('offloading_point')->nullable();
        $table->date('offloading_date')->nullable();
    });
}
};
