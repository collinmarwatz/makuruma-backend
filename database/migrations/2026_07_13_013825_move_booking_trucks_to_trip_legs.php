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
        $table->dropForeign(['trip_id']);
        $table->dropColumn('trip_id');
        $table->foreignId('trip_leg_id')->after('id')->constrained()->cascadeOnDelete();
    });
}

public function down(): void
{
    Schema::table('booking_trucks', function (Blueprint $table) {
        $table->dropForeign(['trip_leg_id']);
        $table->dropColumn('trip_leg_id');
        $table->foreignId('trip_id')->after('id')->constrained();
    });
}
};
