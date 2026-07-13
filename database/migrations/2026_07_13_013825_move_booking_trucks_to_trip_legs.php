<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('booking_trucks', function (Blueprint $table) {
        if (Schema::hasColumn('booking_trucks', 'trip_id')) {
            // Only drop the foreign key if it actually exists
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'booking_trucks'
                AND COLUMN_NAME = 'trip_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            if (count($foreignKeys) > 0) {
                $table->dropForeign(['trip_id']);
            }

            $table->dropColumn('trip_id');
        }
    });

    Schema::table('booking_trucks', function (Blueprint $table) {
        if (!Schema::hasColumn('booking_trucks', 'trip_leg_id')) {
            $table->foreignId('trip_leg_id')->after('id')->constrained()->cascadeOnDelete();
        }
    });
}

public function down(): void
{
    Schema::table('booking_trucks', function (Blueprint $table) {
        if (Schema::hasColumn('booking_trucks', 'trip_leg_id')) {
            $table->dropForeign(['trip_leg_id']);
            $table->dropColumn('trip_leg_id');
        }
    });

    Schema::table('booking_trucks', function (Blueprint $table) {
        if (!Schema::hasColumn('booking_trucks', 'trip_id')) {
            $table->foreignId('trip_id')->after('id')->constrained();
        }
    });
}
};
