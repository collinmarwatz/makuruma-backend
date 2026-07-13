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
    Schema::table('trips', function (Blueprint $table) {
        $table->dropForeign(['truck_id']);
        $table->dropForeign(['trailer_id']);
        $table->dropForeign(['driver_id']);
        $table->dropForeign(['convoy_id']);
        $table->dropColumn(['truck_id', 'trailer_id', 'driver_id', 'convoy_id', 'capacity_override']);
    });
}

public function down(): void
{
    Schema::table('trips', function (Blueprint $table) {
        $table->foreignId('truck_id')->nullable()->constrained();
        $table->foreignId('trailer_id')->nullable()->constrained();
        $table->foreignId('driver_id')->nullable()->constrained();
        $table->foreignId('convoy_id')->nullable()->constrained();
        $table->decimal('capacity_override', 8, 2)->nullable();
    });
}};
