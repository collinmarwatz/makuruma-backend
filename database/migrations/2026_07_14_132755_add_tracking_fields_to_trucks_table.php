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
    Schema::table('trucks', function (Blueprint $table) {
        $table->string('current_location')->nullable()->after('status');
        $table->enum('current_status', ['loading', 'in_transit', 'at_border', 'offloading', 'delayed', 'completed'])
            ->default('loading')->after('current_location');
    });
}

public function down(): void
{
    Schema::table('trucks', function (Blueprint $table) {
        $table->dropColumn(['current_location', 'current_status']);
    });
}
};
