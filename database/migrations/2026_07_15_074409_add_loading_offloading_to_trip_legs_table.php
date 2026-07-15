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
    Schema::table('trip_legs', function (Blueprint $table) {
        $table->string('loading_point')->nullable()->after('location');
        $table->string('offloading_point')->nullable()->after('loading_point');
    });
}

public function down(): void
{
    Schema::table('trip_legs', function (Blueprint $table) {
        $table->dropColumn(['loading_point', 'offloading_point']);
    });
}
};
