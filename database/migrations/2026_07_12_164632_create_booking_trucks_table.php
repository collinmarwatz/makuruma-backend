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
    Schema::create('booking_trucks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
        $table->foreignId('truck_id')->constrained();
        $table->foreignId('trailer_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
        $table->decimal('capacity_override', 8, 2)->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_trucks');
    }
};
