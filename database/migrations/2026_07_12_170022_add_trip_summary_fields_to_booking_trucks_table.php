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
        $table->string('cargo')->nullable()->after('capacity_override');
        $table->string('loading_point')->nullable()->after('cargo');
        $table->date('loading_point_arrival_date')->nullable()->after('loading_point');
        $table->string('offloading_point')->nullable()->after('loading_point_arrival_date');
        $table->date('offloading_date')->nullable()->after('offloading_point');
        $table->decimal('invoiced_transit_weight', 10, 2)->nullable()->after('offloading_date');
        $table->decimal('invoiced_detention_charge', 10, 2)->nullable()->after('invoiced_transit_weight');
    });
}

public function down(): void
{
    Schema::table('booking_trucks', function (Blueprint $table) {
        $table->dropColumn(['cargo', 'loading_point', 'loading_point_arrival_date', 'offloading_point', 'offloading_date', 'invoiced_transit_weight', 'invoiced_detention_charge']);
    });
}
};
