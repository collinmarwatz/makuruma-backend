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
        $table->foreignId('trailer_id')->nullable()->constrained()->nullOnDelete()->after('capacity');
        $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete()->after('trailer_id');
    });
}

public function down(): void
{
    Schema::table('trucks', function (Blueprint $table) {
        $table->dropForeign(['trailer_id']);
        $table->dropForeign(['driver_id']);
        $table->dropColumn(['trailer_id', 'driver_id']);
    });
}
};
